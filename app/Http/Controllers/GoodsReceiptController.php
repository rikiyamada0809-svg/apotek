<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    /**
     * Display a listing of goods receipts.
     */
    public function index(Request $request): View
    {
        $query = GoodsReceipt::with(['supplier', 'warehouse', 'purchaseOrder', 'receiver'])
            ->latest('receipt_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('delivery_note_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', fn ($pq) => $pq->where('po_number', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('receipt_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $goodsReceipts = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('goods-receipts.index', compact('goodsReceipts', 'suppliers'));
    }

    /**
     * Show the form for creating a new goods receipt from a PO.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $poId = $request->input('po_id');

        if (! $poId) {
            // Show list of POs available for receiving
            $availableOrders = PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received'])
                ->with(['supplier', 'warehouse', 'items.product'])
                ->latest('order_date')
                ->get();

            return view('goods-receipts.select-po', compact('availableOrders'));
        }

        $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse', 'items.product.productUnits.unit', 'items.unit'])
            ->findOrFail($poId);

        if (! in_array($purchaseOrder->status, ['approved', 'sent', 'partially_received'])) {
            return redirect()->route('goods-receipts.index')
                ->with('error', 'Status PO ini tidak dapat menerima barang (belum disetujui atau sudah selesai).');
        }

        $storageLocations = StorageLocation::where('warehouse_id', $purchaseOrder->warehouse_id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('goods-receipts.create', compact('purchaseOrder', 'storageLocations'));
    }

    /**
     * Store a newly created goods receipt in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'po_id' => ['required', 'exists:purchase_orders,id'],
            'receipt_date' => ['required', 'date'],
            'delivery_note_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.po_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_number' => ['required', 'string', 'max:50'],
            'items.*.manufacturing_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['required', 'date', 'after:today'],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0'],
            'items.*.storage_location_id' => ['nullable', 'exists:storage_locations,id'],
            'items.*.condition' => ['required', 'in:good,damaged'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($validated['po_id']);

        // Filter items with quantity_received > 0
        $receivedItems = array_filter($validated['items'], fn ($item) => (float) $item['quantity_received'] > 0);

        if (empty($receivedItems)) {
            return back()->withInput()->with('error', 'Minimal harus ada 1 item dengan kuantitas yang diterima lebih dari 0.');
        }

        // Validate that received qty doesn't exceed outstanding qty
        foreach ($receivedItems as $item) {
            $poItem = $purchaseOrder->items->firstWhere('id', $item['po_item_id']);
            if ($poItem) {
                $outstanding = (float) $poItem->quantity_ordered - (float) $poItem->quantity_received;
                if ((float) $item['quantity_received'] > $outstanding) {
                    return back()->withInput()->with('error', "Kuantitas diterima untuk obat {$poItem->product->name} ({$item['quantity_received']}) melebihi sisa pesanan ({$outstanding}).");
                }
            }
        }

        $receipt = $this->purchaseService->receivePO(
            po: $purchaseOrder,
            receiptData: [
                'receipt_date' => $validated['receipt_date'],
                'delivery_note_number' => $validated['delivery_note_number'] ?? null,
                'status' => 'verified',
                'notes' => $validated['notes'] ?? null,
            ],
            receivedItems: $receivedItems,
            userId: auth()->id()
        );

        return redirect()->route('goods-receipts.show', $receipt)
            ->with('success', "Penerimaan barang {$receipt->receipt_number} berhasil diproses dan stok gudang telah diperbarui.");
    }

    /**
     * Display the specified goods receipt.
     */
    public function show(GoodsReceipt $goodsReceipt): View
    {
        $goodsReceipt->load([
            'supplier',
            'warehouse',
            'purchaseOrder',
            'receiver',
            'verifier',
            'items.product',
            'items.batch',
            'items.storageLocation',
            'items.purchaseOrderItem.unit',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    /**
     * Print PDF of the specified goods receipt.
     */
    public function printPdf(GoodsReceipt $goodsReceipt): Response
    {
        $goodsReceipt->load([
            'supplier',
            'warehouse',
            'purchaseOrder',
            'receiver',
            'verifier',
            'items.product',
            'items.batch',
            'items.storageLocation',
        ]);

        $pdf = Pdf::loadView('goods-receipts.pdf', compact('goodsReceipt'));
        $filename = 'Penerimaan-'.$goodsReceipt->receipt_number.'.pdf';

        return $pdf->stream($filename);
    }
}
