<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Services\InvoiceNumberService;
use App\Services\MutationService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected StockService $stockService,
        protected MutationService $mutationService
    ) {}

    /**
     * Display a listing of purchase returns.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $supplierId = $request->input('supplier_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $returnsQuery = PurchaseReturn::with(['purchaseOrder', 'supplier', 'creator', 'approver', 'items'])
            ->when($search, function ($query, $search) {
                $query->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', fn ($q) => $q->where('po_number', 'like', "%{$search}%"));
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($startDate, fn ($q) => $q->whereDate('return_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('return_date', '<=', $endDate))
            ->latest();

        $returns = $returnsQuery->paginate(15)->withQueryString();

        // Summary statistics
        $totalReturns = PurchaseReturn::count();
        $pendingReturns = PurchaseReturn::whereIn('status', ['draft', 'pending'])->count();
        $sentReturns = PurchaseReturn::where('status', 'sent')->count();
        $completedReturns = PurchaseReturn::where('status', 'completed')->count();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('purchase-returns.index', compact(
            'returns',
            'suppliers',
            'totalReturns',
            'pendingReturns',
            'sentReturns',
            'completedReturns'
        ));
    }

    /**
     * Show the form for creating a new purchase return.
     */
    public function create(Request $request): View
    {
        $selectedPoId = $request->input('po_id');
        $selectedPo = null;

        if ($selectedPoId) {
            $selectedPo = PurchaseOrder::with([
                'supplier',
                'warehouse',
                'items.product.productUnits.unit',
                'goodsReceipts.items.batch',
            ])->find($selectedPoId);
        }

        $purchaseOrders = PurchaseOrder::with('supplier')
            ->whereIn('status', ['approved', 'partially_received', 'received'])
            ->latest()
            ->get();

        $returnNumber = $this->invoiceNumberService->generatePurchaseReturnNumber();

        return view('purchase-returns.create', compact('purchaseOrders', 'selectedPo', 'returnNumber', 'selectedPoId'));
    }

    /**
     * Store a newly created purchase return in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'po_id' => ['required', 'exists:purchase_orders,id'],
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'resolution_type' => ['required', 'in:replacement,credit_note,refund'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_id' => ['nullable', 'exists:batches,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.reason' => ['nullable', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $po = PurchaseOrder::findOrFail($validated['po_id']);
        $returnNumber = $this->invoiceNumberService->generatePurchaseReturnNumber();

        $purchaseReturn = DB::transaction(function () use ($validated, $po, $returnNumber) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += (float) $item['quantity'] * (float) $item['unit_price'];
            }

            $return = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'po_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'],
                'status' => 'draft',
                'resolution_type' => $validated['resolution_type'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['quantity'] * (float) $item['unit_price'];
                PurchaseReturnItem::create([
                    'return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'reason' => $item['reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $return;
        });

        return redirect()->route('purchase-returns.show', $purchaseReturn)
            ->with('success', "Retur pembelian {$purchaseReturn->return_number} berhasil dibuat sebagai draft.");
    }

    /**
     * Display the specified purchase return.
     */
    public function show(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load([
            'purchaseOrder.warehouse',
            'supplier',
            'creator',
            'approver',
            'items.product',
            'items.batch',
        ]);

        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    /**
     * Approve the specified purchase return.
     */
    public function approve(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (! in_array($purchaseReturn->status, ['draft', 'pending'])) {
            return back()->with('error', 'Status retur tidak dapat disetujui.');
        }

        $purchaseReturn->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', "Retur pembelian {$purchaseReturn->return_number} berhasil disetujui.");
    }

    /**
     * Send the purchase return to supplier and deduct stock.
     */
    public function send(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if ($purchaseReturn->status !== 'approved') {
            return back()->with('error', 'Retur harus disetujui terlebih dahulu sebelum dikirim.');
        }

        DB::transaction(function () use ($purchaseReturn) {
            $warehouseId = $purchaseReturn->purchaseOrder->warehouse_id;

            foreach ($purchaseReturn->items as $item) {
                // Deduct stock in warehouse
                $this->stockService->updateStock(
                    productId: $item->product_id,
                    warehouseId: $warehouseId,
                    qtyChange: -((float) $item->quantity),
                    batchId: $item->batch_id
                );

                // Deduct batch current_qty if batch exists
                if ($item->batch_id) {
                    Batch::where('id', $item->batch_id)->decrement('current_qty', (float) $item->quantity);
                }

                // Record stock mutation out
                $this->mutationService->recordMutation(
                    productId: $item->product_id,
                    warehouseId: $warehouseId,
                    mutationType: 'out',
                    quantity: (float) $item->quantity,
                    batchId: $item->batch_id,
                    referenceType: 'purchase_return',
                    referenceId: $purchaseReturn->id,
                    notes: "Retur pembelian {$purchaseReturn->return_number} ke supplier",
                    userId: Auth::id()
                );
            }

            $purchaseReturn->update(['status' => 'sent']);
        });

        return back()->with('success', "Retur {$purchaseReturn->return_number} telah ditandai terkirim ke supplier. Stok gudang berhasil dikurangi.");
    }

    /**
     * Resolve and complete the purchase return (e.g. credit note invoice deduction / replacement completed).
     */
    public function resolve(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (! in_array($purchaseReturn->status, ['approved', 'sent'])) {
            return back()->with('error', 'Retur belum dalam status terkirim.');
        }

        DB::transaction(function () use ($purchaseReturn) {
            // If credit note and linked to a PO with unpaid invoice, deduct invoice total_amount
            if ($purchaseReturn->resolution_type === 'credit_note') {
                $invoice = PurchaseInvoice::where('po_id', $purchaseReturn->po_id)
                    ->where('payment_status', '!=', 'paid')
                    ->first();

                if ($invoice) {
                    $newAmount = max(0, (float) $invoice->total_amount - (float) $purchaseReturn->total_amount);
                    $invoice->update(['total_amount' => $newAmount]);
                }
            }

            $purchaseReturn->update(['status' => 'completed']);
        });

        return back()->with('success', "Retur pembelian {$purchaseReturn->return_number} berhasil diselesaikan.");
    }

    /**
     * Print or download PDF slip for purchase return.
     */
    public function printPdf(PurchaseReturn $purchaseReturn): Response
    {
        $purchaseReturn->load([
            'purchaseOrder.warehouse',
            'supplier',
            'creator',
            'approver',
            'items.product',
            'items.batch',
        ]);

        $pdf = Pdf::loadView('purchase-returns.pdf', compact('purchaseReturn'));

        return $pdf->stream("Retur-Pembelian-{$purchaseReturn->return_number}.pdf");
    }
}
