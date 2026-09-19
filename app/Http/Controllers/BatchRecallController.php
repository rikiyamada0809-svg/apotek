<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchRecall;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Services\InvoiceNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchRecallController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService
    ) {}

    /**
     * Display a listing of batch recalls.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $source = $request->input('source');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = BatchRecall::with(['batch.product', 'batch.supplier', 'creator'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($source) {
            $query->where('recall_source', $source);
        }

        if ($startDate) {
            $query->whereDate('recall_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('recall_date', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recall_number', 'like', "%{$search}%")
                    ->orWhere('recall_reason', 'like', "%{$search}%")
                    ->orWhereHas('batch', function ($bq) use ($search) {
                        $bq->where('batch_number', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($pq) use ($search) {
                                $pq->where('name', 'like', "%{$search}%")
                                    ->orWhere('product_code', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $recalls = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => BatchRecall::count(),
            'active' => BatchRecall::whereIn('status', ['quarantined', 'in_progress'])->count(),
            'completed' => BatchRecall::where('status', 'completed')->count(),
            'total_recovered' => (float) BatchRecall::sum('recovered_qty'),
        ];

        return view('batch-recalls.index', compact('recalls', 'stats', 'status', 'source', 'search', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new batch recall.
     */
    public function create(Request $request): View
    {
        $selectedBatchId = $request->input('batch_id');

        // Get batches that can be recalled (not already in completed recall)
        $batches = Batch::with(['product.category', 'supplier', 'stocks'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('batch-recalls.create', compact('batches', 'selectedBatchId'));
    }

    /**
     * Store a newly created batch recall in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'recall_source' => 'required|string|max:100',
            'recall_reason' => 'required|string|max:255',
            'recall_date' => 'required|date',
            'action_taken' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $batch = Batch::findOrFail($request->input('batch_id'));

        $userId = Auth::id() ?? 1;

        $recall = DB::transaction(function () use ($request, $batch, $userId) {
            // 1. Calculate sold quantity from this batch
            $soldQty = (float) SaleItem::where('batch_id', $batch->id)->sum('quantity');

            // 2. Calculate physical stock remaining
            $currentStockQty = (float) Stock::where('batch_id', $batch->id)->sum('quantity');
            $affectedQty = $currentStockQty + $soldQty;

            // 3. Auto-quarantine batch and stocks
            $batch->update(['status' => 'recalled']);

            Stock::where('batch_id', $batch->id)->update([
                'status' => 'quarantined',
                'available_qty' => 0,
            ]);

            // 4. Create BatchRecall record
            return BatchRecall::create([
                'batch_id' => $batch->id,
                'recall_number' => $this->invoiceNumberService->generateRecallNumber(),
                'recall_source' => $request->input('recall_source'),
                'recall_reason' => $request->input('recall_reason'),
                'recall_date' => $request->input('recall_date'),
                'affected_qty' => $affectedQty,
                'sold_qty' => $soldQty,
                'recovered_qty' => 0,
                'status' => 'quarantined',
                'action_taken' => $request->input('action_taken') ?: 'Batch dikarantina di gudang, stok dinonaktifkan dari penjualan POS',
                'notes' => $request->input('notes'),
                'created_by' => $userId,
            ]);
        });

        return redirect()->route('batch-recalls.show', $recall)
            ->with('success', "Penarikan obat ({$recall->recall_number}) berhasil diinisiasi. Batch {$batch->batch_number} dan seluruh stok fisiknya telah otomatis dikarantina.");
    }

    /**
     * Display the specified batch recall with affected transactions and customers.
     */
    public function show(BatchRecall $batchRecall): View
    {
        $batchRecall->load([
            'batch.product.category',
            'batch.product.productUnits.unit',
            'batch.supplier',
            'batch.stocks.warehouse',
            'creator',
        ]);

        // Get all sale items that sold from this batch
        $affectedSaleItems = SaleItem::with(['sale.customer', 'sale.cashier', 'unit'])
            ->where('batch_id', $batchRecall->batch_id)
            ->latest('id')
            ->get();

        // Extract customer contact list
        $customerList = [];
        $unregisteredSoldQty = 0.0;

        foreach ($affectedSaleItems as $item) {
            $customer = $item->sale?->customer;
            $qty = (float) $item->quantity;

            if ($customer) {
                if (! isset($customerList[$customer->id])) {
                    $customerList[$customer->id] = [
                        'customer' => $customer,
                        'total_qty' => 0.0,
                        'invoices' => [],
                    ];
                }
                $customerList[$customer->id]['total_qty'] += $qty;
                $customerList[$customer->id]['invoices'][] = [
                    'invoice_number' => $item->sale->invoice_number,
                    'date' => $item->sale->sale_date,
                    'qty' => $qty,
                ];
            } else {
                $unregisteredSoldQty += $qty;
            }
        }

        $currentStock = (float) $batchRecall->batch?->stocks?->sum('quantity');

        return view('batch-recalls.show', compact(
            'batchRecall',
            'affectedSaleItems',
            'customerList',
            'unregisteredSoldQty',
            'currentStock'
        ));
    }

    /**
     * Update the recovery progress and action taken for a batch recall.
     */
    public function updateProgress(Request $request, BatchRecall $batchRecall): RedirectResponse
    {
        $request->validate([
            'recovered_qty' => 'required|numeric|min:0',
            'status' => 'required|in:quarantined,in_progress,returned_to_supplier,destroyed,completed',
            'action_taken' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $batchRecall->update([
            'recovered_qty' => (float) $request->input('recovered_qty'),
            'status' => $request->input('status'),
            'action_taken' => $request->input('action_taken'),
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', "Progres penarikan obat {$batchRecall->recall_number} berhasil diperbarui.");
    }

    /**
     * Complete the batch recall.
     */
    public function complete(Request $request, BatchRecall $batchRecall): RedirectResponse
    {
        $batchRecall->update([
            'status' => 'completed',
            'action_taken' => $request->input('action_taken') ?: $batchRecall->action_taken,
        ]);

        return back()->with('success', "Proses penarikan obat {$batchRecall->recall_number} telah ditandai selesai.");
    }

    /**
     * Print official Berita Acara Penarikan Obat (Recall) PDF.
     */
    public function printPdf(BatchRecall $batchRecall): Response
    {
        $batchRecall->load([
            'batch.product.category',
            'batch.product.productUnits.unit',
            'batch.supplier',
            'batch.stocks.warehouse',
            'creator',
        ]);

        $affectedSaleItems = SaleItem::with(['sale.customer'])
            ->where('batch_id', $batchRecall->batch_id)
            ->get();

        $currentStock = (float) $batchRecall->batch?->stocks?->sum('quantity');

        $pdf = Pdf::loadView('batch-recalls.pdf', compact('batchRecall', 'affectedSaleItems', 'currentStock'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("laporan-recall-{$batchRecall->recall_number}.pdf");
    }
}
