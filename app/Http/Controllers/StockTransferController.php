<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Services\InvoiceNumberService;
use App\Services\MutationService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected StockService $stockService,
        protected MutationService $mutationService
    ) {}

    /**
     * Display a listing of stock transfers.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $fromWarehouseId = $request->input('from_warehouse_id');
        $toWarehouseId = $request->input('to_warehouse_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'creator', 'items.product'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($fromWarehouseId) {
            $query->where('from_warehouse_id', $fromWarehouseId);
        }

        if ($toWarehouseId) {
            $query->where('to_warehouse_id', $toWarehouseId);
        }

        if ($startDate) {
            $query->whereDate('transfer_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transfer_date', '<=', $endDate);
        }

        if ($search) {
            $query->where('transfer_number', 'like', "%{$search}%");
        }

        $transfers = $query->paginate(15)->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total' => StockTransfer::count(),
            'draft' => StockTransfer::where('status', 'draft')->count(),
            'approved' => StockTransfer::where('status', 'approved')->count(),
            'received' => StockTransfer::where('status', 'received')->count(),
        ];

        return view('stock-transfers.index', compact('transfers', 'warehouses', 'stats', 'status', 'fromWarehouseId', 'toWarehouseId', 'startDate', 'endDate', 'search'));
    }

    /**
     * Show the form for creating a new stock transfer.
     */
    public function create(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        // Get products that have positive stock in any warehouse
        $products = Product::where('is_active', true)
            ->with(['stocks.batch', 'stocks.warehouse', 'productUnits.unit'])
            ->orderBy('name')
            ->get();

        $productsJson = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->product_code,
            'unit' => $p->baseUnit()?->unit?->name ?? 'Pcs',
        ])->values()->all();

        return view('stock-transfers.create', compact('warehouses', 'products', 'productsJson'));
    }

    /**
     * Store a newly created stock transfer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'to_warehouse_id.different' => 'Gudang tujuan harus berbeda dengan gudang asal.',
            'items.required' => 'Minimal satu item harus ditambahkan ke transfer stok.',
            'items.*.quantity.min' => 'Jumlah kuantitas transfer minimal 0.01.',
        ]);

        $fromWarehouseId = (int) $request->input('from_warehouse_id');
        $items = $request->input('items', []);

        // Validate stock availability in source warehouse
        foreach ($items as $index => $item) {
            $productId = (int) $item['product_id'];
            $batchId = (int) $item['batch_id'];
            $qty = (float) $item['quantity'];

            $available = (float) Stock::where('product_id', $productId)
                ->where('warehouse_id', $fromWarehouseId)
                ->where('batch_id', $batchId)
                ->value('available_qty');

            if ($qty > $available) {
                $prod = Product::find($productId);
                $batch = Batch::find($batchId);

                return back()->withInput()->with('error', "Stok tidak mencukupi untuk obat {$prod->name} (Batch: {$batch->batch_number}). Permintaan: {$qty}, Tersedia: {$available}.");
            }
        }

        $userId = Auth::id() ?? 1;

        $stockTransfer = DB::transaction(function () use ($request, $items, $fromWarehouseId, $userId) {
            $transfer = StockTransfer::create([
                'transfer_number' => $this->invoiceNumberService->generateTransferNumber(),
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => (int) $request->input('to_warehouse_id'),
                'transfer_date' => $request->input('transfer_date'),
                'status' => 'draft',
                'notes' => $request->input('notes'),
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => (int) $item['product_id'],
                    'batch_id' => (int) $item['batch_id'],
                    'quantity' => (float) $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $transfer;
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', "Permintaan transfer stok {$stockTransfer->transfer_number} berhasil dibuat (Status: Draft).");
    }

    /**
     * Display the specified stock transfer.
     */
    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load([
            'fromWarehouse',
            'toWarehouse',
            'creator',
            'approver',
            'receiver',
            'items.product.category',
            'items.product.productUnits.unit',
            'items.batch',
        ]);

        return view('stock-transfers.show', compact('stockTransfer'));
    }

    /**
     * Approve the stock transfer.
     */
    public function approve(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'draft') {
            return back()->with('error', 'Hanya transfer dengan status draft yang dapat disetujui.');
        }

        $stockTransfer->update([
            'status' => 'approved',
            'approved_by' => Auth::id() ?? 1,
        ]);

        return back()->with('success', "Transfer stok {$stockTransfer->transfer_number} telah disetujui dan siap dikirim.");
    }

    /**
     * Receive the stock transfer at destination warehouse.
     */
    public function receive(Request $request, StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'approved') {
            return back()->with('error', 'Hanya transfer yang telah disetujui yang dapat diterima.');
        }

        $receivedQuantities = $request->input('received_qty', []);
        $userId = Auth::id() ?? 1;

        $stockTransfer->load(['items', 'fromWarehouse', 'toWarehouse']);

        DB::transaction(function () use ($stockTransfer, $receivedQuantities, $userId) {
            foreach ($stockTransfer->items as $item) {
                $qtyOrdered = (float) $item->quantity;
                $receivedQty = isset($receivedQuantities[$item->id])
                    ? (float) $receivedQuantities[$item->id]
                    : $qtyOrdered;

                // 1. Deduct stock from source warehouse
                $this->stockService->updateStock(
                    $item->product_id,
                    $stockTransfer->from_warehouse_id,
                    -$qtyOrdered,
                    $item->batch_id
                );

                // 2. Record transfer_out mutation for source warehouse
                $this->mutationService->recordMutation(
                    $item->product_id,
                    $stockTransfer->from_warehouse_id,
                    'transfer_out',
                    $qtyOrdered,
                    $item->batch_id,
                    'stock_transfer',
                    $stockTransfer->id,
                    "Transfer keluar ke {$stockTransfer->toWarehouse->name} (#{$stockTransfer->transfer_number})",
                    $userId
                );

                // 3. Increment stock in destination warehouse
                $this->stockService->updateStock(
                    $item->product_id,
                    $stockTransfer->to_warehouse_id,
                    $receivedQty,
                    $item->batch_id
                );

                // 4. Record transfer_in mutation for destination warehouse
                $this->mutationService->recordMutation(
                    $item->product_id,
                    $stockTransfer->to_warehouse_id,
                    'transfer_in',
                    $receivedQty,
                    $item->batch_id,
                    'stock_transfer',
                    $stockTransfer->id,
                    "Transfer masuk dari {$stockTransfer->fromWarehouse->name} (#{$stockTransfer->transfer_number})",
                    $userId
                );

                // Update received quantity
                $item->update([
                    'received_qty' => $receivedQty,
                ]);
            }

            $stockTransfer->update([
                'status' => 'received',
                'received_by' => $userId,
                'received_at' => now(),
            ]);
        });

        return back()->with('success', "Transfer stok {$stockTransfer->transfer_number} telah berhasil diterima. Stok kedua gudang telah diperbarui.");
    }

    /**
     * Cancel the stock transfer.
     */
    public function cancel(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status === 'received') {
            return back()->with('error', 'Transfer stok yang sudah diterima tidak dapat dibatalkan.');
        }

        $stockTransfer->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', "Transfer stok {$stockTransfer->transfer_number} berhasil dibatalkan.");
    }

    /**
     * Print Surat Jalan PDF for stock transfer.
     */
    public function printPdf(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load([
            'fromWarehouse',
            'toWarehouse',
            'creator',
            'approver',
            'receiver',
            'items.product.category',
            'items.product.productUnits.unit',
            'items.batch',
        ]);

        $pdf = Pdf::loadView('stock-transfers.pdf', compact('stockTransfer'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("surat-jalan-transfer-{$stockTransfer->transfer_number}.pdf");
    }

    /**
     * API: Get available batches and stocks for a product in a warehouse.
     */
    public function getWarehouseStock(Request $request): JsonResponse
    {
        $warehouseId = (int) $request->input('warehouse_id');
        $productId = (int) $request->input('product_id');

        $stocks = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('available_qty', '>', 0)
            ->with(['batch'])
            ->get()
            ->map(fn ($s) => [
                'batch_id' => $s->batch_id,
                'batch_number' => $s->batch?->batch_number ?? '-',
                'expiry_date' => $s->batch?->expiry_date?->format('d/m/Y') ?? '-',
                'available_qty' => (float) $s->available_qty,
            ]);

        return response()->json([
            'stocks' => $stocks,
        ]);
    }
}
