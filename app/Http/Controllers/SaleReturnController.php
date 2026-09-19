<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;
use App\Services\InvoiceNumberService;
use App\Services\MutationService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected StockService $stockService,
        protected MutationService $mutationService
    ) {}

    /**
     * Display a listing of sale returns.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $customerId = $request->input('customer_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $returnsQuery = SaleReturn::with(['originalSale', 'customer', 'processor', 'approver', 'items'])
            ->when($search, function ($query, $search) {
                $query->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('originalSale', fn ($q) => $q->where('invoice_number', 'like', "%{$search}%"));
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($startDate, fn ($q) => $q->whereDate('return_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('return_date', '<=', $endDate))
            ->latest();

        $returns = $returnsQuery->paginate(15)->withQueryString();

        // Statistics
        $totalReturns = SaleReturn::count();
        $totalRefundAmount = (float) SaleReturn::where('status', 'completed')->sum('refund_amount');
        $pendingReturns = SaleReturn::whereIn('status', ['draft', 'pending'])->count();
        $completedReturns = SaleReturn::where('status', 'completed')->count();

        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('sale-returns.index', compact(
            'returns',
            'customers',
            'totalReturns',
            'totalRefundAmount',
            'pendingReturns',
            'completedReturns'
        ));
    }

    /**
     * Show the form for creating a new sale return.
     */
    public function create(Request $request): View
    {
        $selectedSaleId = $request->input('sale_id');
        $invoiceNumber = $request->input('invoice_number');
        $selectedSale = null;

        if ($selectedSaleId) {
            $selectedSale = Sale::with(['customer', 'items.product.productUnits.unit', 'items.batch'])->find($selectedSaleId);
        } elseif ($invoiceNumber) {
            $selectedSale = Sale::with(['customer', 'items.product.productUnits.unit', 'items.batch'])
                ->where('invoice_number', $invoiceNumber)
                ->first();
        }

        $isExpiredPolicy = false;
        $daysPassed = 0;
        if ($selectedSale) {
            $daysPassed = Carbon::parse($selectedSale->sale_date)->diffInDays(now());
            if ($daysPassed > 7) {
                $isExpiredPolicy = true;
            }
        }

        $returnNumber = $this->invoiceNumberService->generateSaleReturnNumber();

        return view('sale-returns.create', compact('selectedSale', 'returnNumber', 'isExpiredPolicy', 'daysPassed', 'invoiceNumber'));
    }

    /**
     * Store a newly created sale return in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'original_sale_id' => ['required', 'exists:sales,id'],
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'resolution_type' => ['required', 'in:replacement,refund'],
            'refund_method' => ['nullable', 'in:cash,transfer,credit'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.original_sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_id' => ['nullable', 'exists:batches,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.item_condition' => ['required', 'in:good,damaged,expired'],
            'items.*.return_to_stock' => ['nullable', 'boolean'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $sale = Sale::with('items')->findOrFail($validated['original_sale_id']);

        // Policy check: max 7 days from purchase
        $daysPassed = Carbon::parse($sale->sale_date)->diffInDays(now());
        if ($daysPassed > 7) {
            return back()->withInput()->with('error', "Transaksi telah lewat {$daysPassed} hari. Batas maksimal pengembalian obat adalah 7 hari dari tanggal pembelian.");
        }

        // Validate that returned qty doesn't exceed original sale qty
        foreach ($validated['items'] as $item) {
            $originalItem = $sale->items->firstWhere('id', $item['original_sale_item_id']);
            if ($originalItem && (float) $item['quantity'] > (float) $originalItem->quantity) {
                return back()->withInput()->with('error', "Jumlah obat yang diretur tidak boleh melebihi jumlah transaksi asli ({$originalItem->quantity}).");
            }
        }

        $returnNumber = $this->invoiceNumberService->generateSaleReturnNumber();

        $saleReturn = DB::transaction(function () use ($validated, $sale, $returnNumber) {
            $refundAmount = 0;
            foreach ($validated['items'] as $item) {
                $refundAmount += (float) $item['quantity'] * (float) $item['unit_price'];
            }

            $return = SaleReturn::create([
                'return_number' => $returnNumber,
                'original_sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'],
                'status' => 'draft',
                'resolution_type' => $validated['resolution_type'],
                'refund_amount' => $refundAmount,
                'refund_method' => $validated['refund_method'] ?? 'cash',
                'notes' => $validated['notes'] ?? null,
                'processed_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['quantity'] * (float) $item['unit_price'];
                $returnToStock = ! empty($item['return_to_stock']) && $item['item_condition'] === 'good';

                SaleReturnItem::create([
                    'return_id' => $return->id,
                    'original_sale_item_id' => $item['original_sale_item_id'],
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'item_condition' => $item['item_condition'],
                    'return_to_stock' => $returnToStock,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $return;
        });

        return redirect()->route('sale-returns.show', $saleReturn)
            ->with('success', "Retur penjualan {$saleReturn->return_number} berhasil dibuat sebagai draft.");
    }

    /**
     * Display the specified sale return.
     */
    public function show(SaleReturn $saleReturn): View
    {
        $saleReturn->load([
            'originalSale',
            'customer',
            'processor',
            'approver',
            'items.product',
            'items.batch',
        ]);

        return view('sale-returns.show', compact('saleReturn'));
    }

    /**
     * Approve the specified sale return.
     */
    public function approve(SaleReturn $saleReturn): RedirectResponse
    {
        if (! in_array($saleReturn->status, ['draft', 'pending'])) {
            return back()->with('error', 'Status retur tidak dapat disetujui.');
        }

        $saleReturn->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', "Retur penjualan {$saleReturn->return_number} berhasil disetujui.");
    }

    /**
     * Complete the sale return and restock good condition items.
     */
    public function complete(SaleReturn $saleReturn): RedirectResponse
    {
        if (! in_array($saleReturn->status, ['draft', 'approved'])) {
            return back()->with('error', 'Status retur tidak dapat diselesaikan.');
        }

        DB::transaction(function () use ($saleReturn) {
            $defaultWarehouse = Warehouse::where('is_active', true)->first();
            $warehouseId = $defaultWarehouse ? $defaultWarehouse->id : 1;

            foreach ($saleReturn->items as $item) {
                // If item is in good condition and marked for restock, add back to stock
                if ($item->return_to_stock && $item->item_condition === 'good') {
                    $this->stockService->updateStock(
                        productId: $item->product_id,
                        warehouseId: $warehouseId,
                        qtyChange: (float) $item->quantity,
                        batchId: $item->batch_id
                    );

                    // Increment batch current_qty if batch exists
                    if ($item->batch_id) {
                        Batch::where('id', $item->batch_id)->increment('current_qty', (float) $item->quantity);
                    }

                    // Record stock mutation in
                    $this->mutationService->recordMutation(
                        productId: $item->product_id,
                        warehouseId: $warehouseId,
                        mutationType: 'in',
                        quantity: (float) $item->quantity,
                        batchId: $item->batch_id,
                        referenceType: 'sale_return',
                        referenceId: $saleReturn->id,
                        notes: "Retur konsumen {$saleReturn->return_number} (kondisi baik)",
                        userId: Auth::id()
                    );
                }
            }

            $saleReturn->update(['status' => 'completed']);
        });

        return back()->with('success', "Retur penjualan {$saleReturn->return_number} berhasil diselesaikan. Obat berkondisi baik telah direstok ke inventori.");
    }

    /**
     * Print or download PDF slip for sale return.
     */
    public function printPdf(SaleReturn $saleReturn): Response
    {
        $saleReturn->load([
            'originalSale',
            'customer',
            'processor',
            'approver',
            'items.product',
            'items.batch',
        ]);

        $pdf = Pdf::loadView('sale-returns.pdf', compact('saleReturn'));

        return $pdf->stream("Retur-Penjualan-{$saleReturn->return_number}.pdf");
    }
}
