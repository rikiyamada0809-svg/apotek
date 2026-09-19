<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StockSetting;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Display stock overview with multi-tab view (products, batches, warehouses).
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'products');
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $warehouseId = $request->input('warehouse_id');
        $stockStatus = $request->input('stock_status');

        // General KPI Metrics
        $totalProducts = Product::where('is_active', true)->count();
        $totalStockQty = (float) Stock::sum('quantity');

        // Expiring batches in next 30 days or already expired with stock
        $expiringBatchesCount = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        // Low stock products count
        $allProductsWithStock = Product::with(['stocks', 'stockSettings'])->get();
        $lowStockCount = $allProductsWithStock->filter(function ($prod) {
            $avail = (float) $prod->stocks->sum('available_qty');
            $setting = $prod->stockSettings->first();
            $min = $setting ? (float) $setting->min_stock : 10;

            return $avail <= $min;
        })->count();

        $categories = Category::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        $products = null;
        $batches = null;
        $warehouseStocks = null;

        if ($tab === 'batches') {
            $batches = Batch::with(['product.category', 'supplier', 'stocks.warehouse', 'stocks.storageLocation'])
                ->when($search, function ($query, $search) {
                    $query->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%")
                                ->orWhere('product_code', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                })
                ->when($warehouseId, function ($query, $warehouseId) {
                    $query->whereHas('stocks', function ($sq) use ($warehouseId) {
                        $sq->where('warehouse_id', $warehouseId);
                    });
                })
                ->when($stockStatus, function ($query, $status) {
                    if ($status === 'expired') {
                        $query->where('expiry_date', '<', now()->toDateString());
                    } elseif ($status === 'expiring_soon') {
                        $query->where('status', 'active')
                            ->where('current_qty', '>', 0)
                            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
                    } elseif ($status === 'depleted') {
                        $query->where('current_qty', '<=', 0);
                    } elseif ($status === 'active') {
                        $query->where('status', 'active')->where('current_qty', '>', 0)->where('expiry_date', '>', now()->addDays(30)->toDateString());
                    }
                })
                ->orderBy('expiry_date', 'asc')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'warehouses') {
            $warehouseStocks = Warehouse::with(['storageLocations.stocks.product', 'storageLocations.stocks.batch'])
                ->when($warehouseId, fn ($q) => $q->where('id', $warehouseId))
                ->active()
                ->get();
        } else {
            // Tab products
            $productsQuery = Product::with(['category', 'productUnits.unit', 'stocks.warehouse', 'stockSettings.warehouse'])
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%");
                })
                ->when($categoryId, function ($query, $catId) {
                    $query->where('category_id', $catId);
                })
                ->latest();

            $products = $productsQuery->paginate(15)->withQueryString();

            // Decorate products with stock calculations
            $products->getCollection()->transform(function ($product) use ($warehouseId) {
                $stocks = $product->stocks;
                if ($warehouseId) {
                    $stocks = $stocks->where('warehouse_id', $warehouseId);
                }

                $totalQty = (float) $stocks->sum('quantity');
                $reservedQty = (float) $stocks->sum('reserved_qty');
                $availableQty = (float) $stocks->sum('available_qty');

                $setting = $product->stockSettings;
                if ($warehouseId) {
                    $setting = $setting->where('warehouse_id', $warehouseId)->first();
                } else {
                    $setting = $setting->first();
                }

                $minStock = $setting ? (float) $setting->min_stock : 10;
                $maxStock = $setting ? (float) $setting->max_stock : 1000;
                $reorderPoint = $setting ? (float) $setting->reorder_point : 15;

                if ($availableQty <= 0) {
                    $status = 'out_of_stock';
                    $statusLabel = 'Habis';
                } elseif ($availableQty <= $minStock) {
                    $status = 'low_stock';
                    $statusLabel = 'Kritis / Menipis';
                } else {
                    $status = 'available';
                    $statusLabel = 'Aman';
                }

                $baseUnit = $product->productUnits->firstWhere('is_base_unit', true);

                $product->calculated_total_qty = $totalQty;
                $product->calculated_reserved_qty = $reservedQty;
                $product->calculated_available_qty = $availableQty;
                $product->stock_status = $status;
                $product->stock_status_label = $statusLabel;
                $product->min_stock = $minStock;
                $product->max_stock = $maxStock;
                $product->reorder_point = $reorderPoint;
                $product->base_unit_name = $baseUnit?->unit?->name ?? 'Unit';

                return $product;
            });

            // Filter collection by stock_status if requested
            if ($stockStatus && in_array($stockStatus, ['available', 'low_stock', 'out_of_stock'])) {
                $filtered = $products->getCollection()->filter(fn ($p) => $p->stock_status === $stockStatus);
                $products->setCollection($filtered);
            }
        }

        return view('stocks.index', compact(
            'tab',
            'totalProducts',
            'totalStockQty',
            'lowStockCount',
            'expiringBatchesCount',
            'categories',
            'warehouses',
            'products',
            'batches',
            'warehouseStocks'
        ));
    }

    /**
     * Display digital stock card for a specific product and date range.
     */
    public function card(Request $request): View
    {
        $products = Product::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        $productId = $request->input('product_id') ?: $products->first()?->id;
        $warehouseId = $request->input('warehouse_id');
        $batchId = $request->input('batch_id');

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $selectedProduct = null;
        $productBatches = collect();
        $mutations = collect();
        $initialBalance = 0;
        $totalIn = 0;
        $totalOut = 0;
        $finalBalance = 0;

        if ($productId) {
            $selectedProduct = Product::with(['category', 'productUnits.unit', 'stocks.warehouse'])->find($productId);

            if ($selectedProduct) {
                $productBatches = Batch::where('product_id', $selectedProduct->id)->orderBy('expiry_date', 'asc')->get();

                // Compute initial balance before $startDate
                $priorMutations = StockMutation::where('product_id', $selectedProduct->id)
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
                    ->where('created_at', '<', Carbon::parse($startDate)->startOfDay())
                    ->get();

                foreach ($priorMutations as $mut) {
                    if (in_array($mut->mutation_type, ['in', 'adjustment_plus', 'return_in'])) {
                        $initialBalance += (float) $mut->quantity;
                    } elseif (in_array($mut->mutation_type, ['out', 'disposal', 'expired', 'return_out'])) {
                        $initialBalance -= (float) $mut->quantity;
                    } else {
                        $initialBalance += ((float) $mut->stock_after - (float) $mut->stock_before);
                    }
                }
                $initialBalance = max(0, $initialBalance);

                // Fetch mutations within date range
                $rawMutations = StockMutation::with(['creator', 'batch', 'warehouse'])
                    ->where('product_id', $selectedProduct->id)
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
                    ->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay(),
                    ])
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $currentRunning = $initialBalance;
                $mutations = $rawMutations->map(function ($mutation) use (&$currentRunning, &$totalIn, &$totalOut) {
                    $isIn = in_array($mutation->mutation_type, ['in', 'adjustment_plus', 'return_in']);
                    $isOut = in_array($mutation->mutation_type, ['out', 'disposal', 'expired', 'return_out']);
                    $qty = (float) $mutation->quantity;

                    if ($isIn) {
                        $currentRunning += $qty;
                        $totalIn += $qty;
                        $qtyIn = $qty;
                        $qtyOut = 0;
                    } elseif ($isOut) {
                        $currentRunning = max(0, $currentRunning - $qty);
                        $totalOut += $qty;
                        $qtyIn = 0;
                        $qtyOut = $qty;
                    } else {
                        $diff = (float) $mutation->stock_after - (float) $mutation->stock_before;
                        if ($diff >= 0) {
                            $currentRunning += $diff;
                            $totalIn += $diff;
                            $qtyIn = $diff;
                            $qtyOut = 0;
                        } else {
                            $currentRunning = max(0, $currentRunning + $diff);
                            $totalOut += abs($diff);
                            $qtyIn = 0;
                            $qtyOut = abs($diff);
                        }
                    }

                    return (object) [
                        'id' => $mutation->id,
                        'date' => $mutation->created_at,
                        'mutation_type' => $mutation->mutation_type,
                        'reference_type' => $mutation->reference_type,
                        'reference_id' => $mutation->reference_id,
                        'batch_number' => $mutation->batch?->batch_number ?? '-',
                        'warehouse_name' => $mutation->warehouse?->name ?? '-',
                        'qty_in' => $qtyIn,
                        'qty_out' => $qtyOut,
                        'balance' => $currentRunning,
                        'creator_name' => $mutation->creator?->name ?? 'Sistem',
                        'notes' => $mutation->notes,
                    ];
                });

                $finalBalance = $currentRunning;
            }
        }

        return view('stocks.card', compact(
            'products',
            'warehouses',
            'selectedProduct',
            'productBatches',
            'mutations',
            'initialBalance',
            'totalIn',
            'totalOut',
            'finalBalance',
            'productId',
            'warehouseId',
            'batchId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Update stock settings (min/max stock, reorder point) for a product and warehouse.
     */
    public function updateSettings(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'max_stock' => ['required', 'numeric', 'min:0'],
            'reorder_point' => ['required', 'numeric', 'min:0'],
            'safety_stock' => ['nullable', 'numeric', 'min:0'],
        ]);

        StockSetting::updateOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $validated['warehouse_id'],
            ],
            [
                'min_stock' => $validated['min_stock'],
                'max_stock' => $validated['max_stock'],
                'reorder_point' => $validated['reorder_point'],
                'safety_stock' => $validated['safety_stock'] ?? 0,
            ]
        );

        return back()->with('success', "Batas stok untuk {$product->name} berhasil diperbarui.");
    }
}
