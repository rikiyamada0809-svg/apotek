<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\InvoiceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseInvoiceController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService
    ) {}

    /**
     * Display a listing of purchase invoices.
     */
    public function index(Request $request): View
    {
        $query = PurchaseInvoice::with(['supplier', 'purchaseOrder', 'payments'])
            ->latest('invoice_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('purchaseOrder', fn ($pq) => $pq->where('po_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $invoices = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchase-invoices.index', compact('invoices', 'suppliers'));
    }

    /**
     * Show the form for creating a new purchase invoice.
     */
    public function create(Request $request): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received', 'received'])
            ->latest('order_date')
            ->get();

        $selectedPo = null;
        if ($request->filled('po_id')) {
            $selectedPo = PurchaseOrder::with('supplier')->find($request->input('po_id'));
        }

        $suggestedNumber = $this->invoiceNumberService->generateInvoiceNumber();

        return view('purchase-invoices.create', compact('suppliers', 'purchaseOrders', 'selectedPo', 'suggestedNumber'));
    }

    /**
     * Store a newly created purchase invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:50', 'unique:purchase_invoices,invoice_number'],
            'po_id' => ['nullable', 'exists:purchase_orders,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = PurchaseInvoice::create([
            'invoice_number' => $validated['invoice_number'],
            'po_id' => $validated['po_id'] ?? null,
            'supplier_id' => $validated['supplier_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'total_amount' => $validated['total_amount'],
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('purchase-invoices.show', $invoice)
            ->with('success', "Faktur pembelian {$invoice->invoice_number} berhasil dicatat.");
    }

    /**
     * Display the specified purchase invoice.
     */
    public function show(PurchaseInvoice $purchaseInvoice): View
    {
        $purchaseInvoice->load(['supplier', 'purchaseOrder.items.product', 'payments.creator']);

        return view('purchase-invoices.show', compact('purchaseInvoice'));
    }
}
