<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\InvoiceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockReorderController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService
    ) {}

    /**
     * Display low stock recommendations and reorder dashboard.
     */
    public function index(Request $request): View
    {
        $warehouseId = $request->input('warehouse_id');
        $categoryId = $request->input('category_id');
        $search = $request->input('search');
        $statusFilter = $request->input('status'); // 'all', 'out_of_stock', 'low_stock'

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $selectedWarehouse = $warehouseId ? Warehouse::find($warehouseId) : $warehouses->first();
        $selectedWarehouseId = $selectedWarehouse?->id;

        $productsQuery = Product::where('is_active', true)
            ->with([
                'category',
                'productUnits.unit',
                'batches' => fn ($q) => $q->whereNotNull('supplier_id')->latest(),
                'stockSettings' => function ($q) use ($selectedWarehouseId) {
                    if ($selectedWarehouseId) {
                        $q->where('warehouse_id', $selectedWarehouseId);
                    }
                },
                'stocks' => function ($q) use ($selectedWarehouseId) {
                    if ($selectedWarehouseId) {
                        $q->where('warehouse_id', $selectedWarehouseId);
                    }
                },
            ]);

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        if ($search) {
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $allProducts = $productsQuery->get();

        $reorderItems = [];
        $outOfStockCount = 0;
        $totalEstimatedBudget = 0.0;
        $supplierIds = [];

        foreach ($allProducts as $product) {
            $currentStock = (float) $product->stocks->sum('available_qty');
            $setting = $product->stockSettings->first();

            $rop = $setting?->reorder_point ?? $setting?->min_stock ?? 10;
            $maxStock = $setting?->max_stock ?? max($rop * 2, 20);

            $isOutOfStock = $currentStock <= 0;
            $isBelowRop = $currentStock <= $rop;

            if ($statusFilter === 'out_of_stock' && ! $isOutOfStock) {
                continue;
            }

            // By default or low_stock, we only want items at or below ROP unless user specifically searched
            if (! $search && ! $isBelowRop) {
                continue;
            }

            if ($isOutOfStock) {
                $outOfStockCount++;
            }

            $deficiency = max(0, $maxStock - $currentStock);
            $suggestedQty = max($deficiency, (float) ($product->min_order_qty ?: 1));
            $purchasePrice = (float) $product->purchase_price;
            $estimatedCost = $suggestedQty * $purchasePrice;

            if ($isBelowRop) {
                $totalEstimatedBudget += $estimatedCost;
            }

            // Find best supplier
            $batchSupplier = $product->batches->first()?->supplier;
            $supplier = $batchSupplier ?? Supplier::first();
            if ($supplier) {
                $supplierIds[$supplier->id] = true;
            }

            $reorderItems[] = (object) [
                'product_id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->name,
                'category_name' => $product->category?->name ?? '-',
                'warehouse_id' => $selectedWarehouseId,
                'warehouse_name' => $selectedWarehouse?->name ?? 'Semua Gudang',
                'current_stock' => $currentStock,
                'min_stock' => $setting?->min_stock ?? 5,
                'reorder_point' => $rop,
                'max_stock' => $maxStock,
                'suggested_qty' => $suggestedQty,
                'purchase_price' => $purchasePrice,
                'estimated_cost' => $estimatedCost,
                'supplier_id' => $supplier?->id,
                'supplier_name' => $supplier?->name ?? 'Belum Ditentukan',
                'lead_time_days' => $setting?->lead_time_days ?? 3,
                'is_out_of_stock' => $isOutOfStock,
                'is_below_rop' => $isBelowRop,
                'base_unit' => $product->baseUnit()?->unit?->name ?? 'Pcs',
            ];
        }

        $stats = [
            'total_reorder_items' => count(array_filter($reorderItems, fn ($i) => $i->is_below_rop)),
            'out_of_stock_count' => $outOfStockCount,
            'total_estimated_budget' => $totalEstimatedBudget,
            'suppliers_count' => count($supplierIds),
        ];

        return view('stocks.reorder', compact(
            'reorderItems',
            'warehouses',
            'categories',
            'selectedWarehouseId',
            'stats',
            'statusFilter',
            'search',
            'categoryId'
        ));
    }

    /**
     * Generate draft Purchase Orders from low stock recommendations.
     */
    public function generatePo(Request $request): RedirectResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'selected_products' => 'nullable|array',
            'selected_products.*' => 'exists:products,id',
            'quantities' => 'nullable|array',
        ]);

        $warehouseId = (int) $request->input('warehouse_id');
        $selectedProductIds = $request->input('selected_products', []);
        $quantities = $request->input('quantities', []);

        if (empty($selectedProductIds)) {
            return back()->with('error', 'Silakan pilih minimal 1 item obat untuk di-reorder.');
        }

        $products = Product::whereIn('id', $selectedProductIds)
            ->with([
                'batches' => fn ($q) => $q->whereNotNull('supplier_id')->latest(),
                'stockSettings' => fn ($q) => $q->where('warehouse_id', $warehouseId),
            ])
            ->get();

        // Group by supplier
        $grouped = [];
        $defaultSupplier = Supplier::first();

        foreach ($products as $product) {
            $supplier = $product->batches->first()?->supplier ?? $defaultSupplier;
            if (! $supplier) {
                continue;
            }

            $qty = isset($quantities[$product->id]) && (float) $quantities[$product->id] > 0
                ? (float) $quantities[$product->id]
                : max(1, (float) ($product->min_order_qty ?: 1));

            $grouped[$supplier->id][] = [
                'product' => $product,
                'supplier' => $supplier,
                'quantity' => $qty,
                'price' => (float) $product->purchase_price,
            ];
        }

        if (empty($grouped)) {
            return back()->with('error', 'Gagal membuat PO. Data supplier obat belum tersedia.');
        }

        $userId = Auth::id() ?? 1;
        $createdPoCount = 0;
        $defaultUnitId = Unit::value('id');

        DB::transaction(function () use ($grouped, $warehouseId, $userId, $defaultUnitId, &$createdPoCount) {
            foreach ($grouped as $supplierId => $items) {
                $po = PurchaseOrder::create([
                    'po_number' => $this->invoiceNumberService->generatePoNumber(),
                    'supplier_id' => $supplierId,
                    'warehouse_id' => $warehouseId,
                    'order_date' => now()->toDateString(),
                    'expected_delivery_date' => now()->addDays(3)->toDateString(),
                    'status' => 'draft',
                    'subtotal' => 0,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'grand_total' => 0,
                    'notes' => 'Generated automatically by Auto-Reorder recommendation system',
                    'created_by' => $userId,
                ]);

                $subtotal = 0;
                foreach ($items as $item) {
                    $prod = $item['product'];
                    $unitId = $prod->baseUnit()?->unit_id ?? $defaultUnitId;
                    $itemTotal = $item['price'] * $item['quantity'];

                    PurchaseOrderItem::create([
                        'po_id' => $po->id,
                        'product_id' => $prod->id,
                        'unit_id' => $unitId,
                        'quantity_ordered' => $item['quantity'],
                        'quantity_received' => 0,
                        'unit_price' => $item['price'],
                        'discount' => 0,
                        'subtotal' => $itemTotal,
                        'notes' => 'Auto-reorder draft',
                    ]);

                    $subtotal += $itemTotal;
                }

                $po->update([
                    'subtotal' => $subtotal,
                    'grand_total' => $subtotal,
                ]);

                $createdPoCount++;
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', "Berhasil membuat {$createdPoCount} draft Surat Pesanan (PO) dari rekomendasi Auto-Reorder.");
    }
}
