<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'creator'])
            ->latest('order_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('order_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create(Request $request): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()
            ->with(['productUnits.unit', 'category'])
            ->orderBy('name')
            ->get();

        $prefilledData = [];
        if ($request->filled('duplicate_from')) {
            $sourcePo = PurchaseOrder::with('items')->find($request->input('duplicate_from'));
            if ($sourcePo) {
                $prefilledData = [
                    'supplier_id' => $sourcePo->supplier_id,
                    'warehouse_id' => $sourcePo->warehouse_id,
                    'discount_amount' => $sourcePo->discount_amount,
                    'tax_amount' => $sourcePo->tax_amount,
                    'notes' => $sourcePo->notes,
                    'items' => $sourcePo->items->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'unit_id' => $item->unit_id,
                        'quantity_ordered' => (float) $item->quantity_ordered,
                        'unit_price' => (float) $item->unit_price,
                        'discount' => (float) $item->discount,
                    ])->toArray(),
                ];
            }
        }

        return view('purchase-orders.create', compact('suppliers', 'warehouses', 'products', 'prefilledData'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $po = $this->purchaseService->createPO(
            data: $validated,
            items: $validated['items'],
            userId: auth()->id()
        );

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "Purchase Order {$po->po_number} berhasil dibuat.");
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'approver',
            'creator',
            'items.product',
            'items.unit',
            'goodsReceipts.receiver',
            'invoices.payments',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order.
     */
    public function edit(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (! in_array($purchaseOrder->status, ['draft', 'pending_approval'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order yang sudah disetujui atau diproses tidak dapat diedit.');
        }

        $purchaseOrder->load('items');
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()
            ->with(['productUnits.unit', 'category'])
            ->orderBy('name')
            ->get();

        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products'));
    }

    /**
     * Update the specified purchase order.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (! in_array($purchaseOrder->status, ['draft', 'pending_approval'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order tidak dapat diedit.');
        }

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $validated) {
            $subtotal = 0;
            $discountAmount = (float) ($validated['discount_amount'] ?? 0);
            $taxAmount = (float) ($validated['tax_amount'] ?? 0);

            // Delete old items and insert updated items
            $purchaseOrder->items()->delete();

            foreach ($validated['items'] as $item) {
                $itemSubtotal = ((float) $item['quantity_ordered'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0);
                $subtotal += max(0, $itemSubtotal);

                PurchaseOrderItem::create([
                    'po_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => max(0, $itemSubtotal),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $grandTotal = max(0, $subtotal - $discountAmount + $taxAmount);

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order berhasil diperbarui.');
    }

    /**
     * Approve the specified purchase order.
     */
    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'pending_approval' && $purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Status PO saat ini tidak dapat disetujui.');
        }

        $this->purchaseService->approvePO($purchaseOrder, auth()->id());

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "PO {$purchaseOrder->po_number} berhasil disetujui.");
    }

    /**
     * Cancel the specified purchase order.
     */
    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'PO ini tidak dapat dibatalkan.');
        }

        $this->purchaseService->cancelPO($purchaseOrder);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "PO {$purchaseOrder->po_number} telah dibatalkan.");
    }

    /**
     * Print PDF of the specified purchase order.
     */
    public function printPdf(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product', 'items.unit', 'approver', 'creator']);

        $pdf = Pdf::loadView('purchase-orders.pdf', compact('purchaseOrder'));
        $filename = 'PO-'.$purchaseOrder->po_number.'.pdf';

        return $pdf->stream($filename);
    }
}
