<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\DrugDestruction;
use App\Models\DrugDestructionItem;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\InvoiceNumberService;
use App\Services\MutationService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DrugDestructionController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected StockService $stockService,
        protected MutationService $mutationService
    ) {}

    /**
     * Display a listing of drug destructions.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $method = $request->input('method');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DrugDestruction::with(['executor', 'approver', 'items.product', 'items.batch'])
            ->when($search, function ($q, $search) {
                $q->where('destruction_number', 'like', "%{$search}%")
                    ->orWhere('witness_name', 'like', "%{$search}%")
                    ->orWhere('witness_role', 'like', "%{$search}%");
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($method, fn ($q) => $q->where('method', $method))
            ->when($startDate, fn ($q) => $q->whereDate('destruction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('destruction_date', '<=', $endDate))
            ->latest('destruction_date')
            ->latest('id');

        $destructions = $query->paginate(15)->withQueryString();

        $totalDestructions = DrugDestruction::count();
        $pendingDestructions = DrugDestruction::whereIn('status', ['draft', 'pending'])->count();
        $approvedDestructions = DrugDestruction::where('status', 'approved')->count();
        $completedDestructions = DrugDestruction::where('status', 'completed')->count();
        $totalLossAmount = (float) DrugDestruction::where('status', 'completed')->sum('total_loss_amount');

        return view('drug-destructions.index', compact(
            'destructions',
            'totalDestructions',
            'pendingDestructions',
            'approvedDestructions',
            'completedDestructions',
            'totalLossAmount'
        ));
    }

    /**
     * Show the form for creating a new drug destruction.
     */
    public function create(Request $request): View
    {
        $destructionNumber = $this->invoiceNumberService->generateDestructionNumber();

        // Load batches with stock available, prioritizing expired or near-expired batches
        $batches = Batch::with(['product.category', 'product.productUnits.unit', 'stocks.warehouse', 'supplier'])
            ->where('current_qty', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('drug-destructions.create', compact('destructionNumber', 'batches', 'warehouses'));
    }

    /**
     * Store a newly created drug destruction in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destruction_date' => ['required', 'date'],
            'witness_name' => ['required', 'string', 'max:255'],
            'witness_role' => ['required', 'string', 'max:100'],
            'method' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_id' => ['required', 'exists:batches,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.reason' => ['required', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('destructions', 'public');
        }

        $destructionNumber = $this->invoiceNumberService->generateDestructionNumber();

        $destruction = DB::transaction(function () use ($validated, $destructionNumber, $documentPath) {
            $record = DrugDestruction::create([
                'destruction_number' => $destructionNumber,
                'destruction_date' => $validated['destruction_date'],
                'witness_name' => $validated['witness_name'],
                'witness_role' => $validated['witness_role'],
                'method' => $validated['method'],
                'status' => 'draft',
                'total_loss_amount' => 0,
                'document_path' => $documentPath,
                'notes' => $validated['notes'] ?? null,
                'executed_by' => Auth::id(),
            ]);

            $totalLoss = 0;

            foreach ($validated['items'] as $itemData) {
                $batch = Batch::findOrFail($itemData['batch_id']);
                $qty = (float) $itemData['quantity'];
                $unitPrice = (float) $batch->purchase_price;
                $lossAmount = $qty * $unitPrice;
                $totalLoss += $lossAmount;

                DrugDestructionItem::create([
                    'destruction_id' => $record->id,
                    'product_id' => $batch->product_id,
                    'batch_id' => $batch->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'loss_amount' => $lossAmount,
                    'reason' => $itemData['reason'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            $record->update(['total_loss_amount' => $totalLoss]);

            return $record;
        });

        return redirect()->route('drug-destructions.show', $destruction)
            ->with('success', "Berita acara pemusnahan {$destruction->destruction_number} berhasil dibuat sebagai draft.");
    }

    /**
     * Display the specified drug destruction.
     */
    public function show(DrugDestruction $drugDestruction): View
    {
        $drugDestruction->load([
            'items.product.category',
            'items.product.productUnits.unit',
            'items.batch.stocks.warehouse',
            'executor',
            'approver',
        ]);

        return view('drug-destructions.show', compact('drugDestruction'));
    }

    /**
     * Approve the drug destruction.
     */
    public function approve(DrugDestruction $drugDestruction): RedirectResponse
    {
        if (! in_array($drugDestruction->status, ['draft', 'pending'])) {
            return back()->with('error', 'Status pemusnahan tidak dapat disetujui.');
        }

        $drugDestruction->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', "Pemusnahan obat {$drugDestruction->destruction_number} telah disetujui.");
    }

    /**
     * Execute and complete the drug destruction, reducing stock and creating disposal mutations.
     */
    public function complete(DrugDestruction $drugDestruction): RedirectResponse
    {
        if ($drugDestruction->status !== 'approved') {
            return back()->with('error', 'Pemusnahan obat harus disetujui terlebih dahulu sebelum dieksekusi.');
        }

        DB::transaction(function () use ($drugDestruction) {
            foreach ($drugDestruction->items as $item) {
                $batch = Batch::findOrFail($item->batch_id);
                $qtyToDeduct = (float) $item->quantity;

                // Find warehouse where stock is located
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('batch_id', $item->batch_id)
                    ->first();

                $warehouseId = $stock?->warehouse_id ?? 1;

                // Update warehouse stock
                $this->stockService->updateStock(
                    productId: $item->product_id,
                    warehouseId: $warehouseId,
                    qtyChange: -$qtyToDeduct,
                    batchId: $item->batch_id
                );

                // Update batch current quantity
                $batch->decrement('current_qty', $qtyToDeduct);
                if ((float) $batch->fresh()->current_qty <= 0 && $batch->status === 'active') {
                    $batch->update(['status' => 'exhausted']);
                }

                // Record disposal mutation
                $this->mutationService->recordMutation(
                    productId: $item->product_id,
                    warehouseId: $warehouseId,
                    mutationType: 'disposal',
                    quantity: $qtyToDeduct,
                    batchId: $item->batch_id,
                    referenceType: 'drug_destruction',
                    referenceId: $drugDestruction->id,
                    notes: "Pemusnahan Obat {$drugDestruction->destruction_number}: {$item->reason}",
                    userId: Auth::id()
                );
            }

            $drugDestruction->update([
                'status' => 'completed',
                'executed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', "Pemusnahan obat {$drugDestruction->destruction_number} berhasil dieksekusi. Stok obat telah dikurangi dan mutasi pemusnahan (disposal) dicatat.");
    }

    /**
     * Upload or update signed document scan.
     */
    public function uploadDocument(Request $request, DrugDestruction $drugDestruction): RedirectResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($drugDestruction->document_path) {
            Storage::disk('public')->delete($drugDestruction->document_path);
        }

        $path = $request->file('document')->store('destructions', 'public');
        $drugDestruction->update(['document_path' => $path]);

        return back()->with('success', 'Dokumen Berita Acara Pemusnahan berhasil diunggah.');
    }

    /**
     * Print or download PDF report for drug destruction.
     */
    public function printPdf(DrugDestruction $drugDestruction): Response
    {
        $drugDestruction->load([
            'items.product.category',
            'items.product.productUnits.unit',
            'items.batch',
            'executor',
            'approver',
        ]);

        $pdf = Pdf::loadView('drug-destructions.pdf', compact('drugDestruction'));

        return $pdf->stream("Berita-Acara-Pemusnahan-{$drugDestruction->destruction_number}.pdf");
    }
}
