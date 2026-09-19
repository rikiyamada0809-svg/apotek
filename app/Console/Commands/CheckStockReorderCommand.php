<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InvoiceNumberService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckStockReorderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-reorder {--generate-po : Automatically generate draft Purchase Orders for items below reorder point} {--warehouse= : Check specific warehouse ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inventory levels against reorder points and optionally generate draft Purchase Orders';

    public function __construct(
        protected InvoiceNumberService $invoiceNumberService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking stock levels against reorder points...');

        $warehouseId = $this->option('warehouse');
        $generatePo = $this->option('generate-po');

        $warehousesQuery = Warehouse::query();
        if ($warehouseId) {
            $warehousesQuery->where('id', $warehouseId);
        }
        $warehouses = $warehousesQuery->get();

        if ($warehouses->isEmpty()) {
            $this->warn('No warehouses found to check.');

            return self::SUCCESS;
        }

        $lowStockItems = [];

        foreach ($warehouses as $warehouse) {
            // Check products that have stock settings for this warehouse, or all active products
            $products = Product::where('is_active', true)->with(['stockSettings' => function ($q) use ($warehouse) {
                $q->where('warehouse_id', $warehouse->id);
            }, 'batches' => function ($q) {
                $q->whereNotNull('supplier_id')->latest();
            }])->get();

            foreach ($products as $product) {
                $setting = $product->stockSettings->first();
                $rop = $setting?->reorder_point ?? $setting?->min_stock ?? 10;
                $maxStock = $setting?->max_stock ?? max($rop * 2, 20);

                $currentStock = (float) Stock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('available_qty');

                if ($currentStock <= $rop) {
                    $deficiency = max(0, $maxStock - $currentStock);
                    $suggestedQty = max($deficiency, (float) ($product->min_order_qty ?: 1));

                    // Determine supplier: product latest batch supplier, or any supplier
                    $supplierId = $product->batches->first()?->supplier_id ?? Supplier::value('id');

                    $lowStockItems[] = [
                        'product_id' => $product->id,
                        'product_code' => $product->product_code,
                        'product_name' => $product->name,
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'current_stock' => $currentStock,
                        'reorder_point' => $rop,
                        'max_stock' => $maxStock,
                        'suggested_qty' => $suggestedQty,
                        'supplier_id' => $supplierId,
                        'purchase_price' => $product->purchase_price,
                        'lead_time_days' => $setting?->lead_time_days ?? 3,
                    ];
                }
            }
        }

        if (empty($lowStockItems)) {
            $this->info('All products are currently above their reorder points. No reordering needed.');

            return self::SUCCESS;
        }

        $this->table(
            ['Product Code', 'Product Name', 'Warehouse', 'Current Stock', 'ROP', 'Suggested Order'],
            array_map(fn ($item) => [
                $item['product_code'],
                $item['product_name'],
                $item['warehouse_name'],
                $item['current_stock'],
                $item['reorder_point'],
                $item['suggested_qty'],
            ], $lowStockItems)
        );

        if ($generatePo) {
            $this->info('Generating draft Purchase Orders...');
            $createdCount = $this->generatePurchaseOrders($lowStockItems);
            $this->info("Successfully generated {$createdCount} draft Purchase Order(s).");
        }

        return self::SUCCESS;
    }

    /**
     * Group low stock items by supplier and warehouse, then generate draft POs.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function generatePurchaseOrders(array $items): int
    {
        $grouped = [];
        foreach ($items as $item) {
            $supplierId = $item['supplier_id'];
            if (! $supplierId) {
                continue;
            }
            $warehouseId = $item['warehouse_id'];
            $key = "{$supplierId}_{$warehouseId}";
            $grouped[$key][] = $item;
        }

        $adminUser = User::first();
        $userId = $adminUser?->id ?? 1;
        $createdPoCount = 0;

        foreach ($grouped as $key => $groupItems) {
            DB::transaction(function () use ($groupItems, $userId, &$createdPoCount) {
                $first = $groupItems[0];
                $supplierId = $first['supplier_id'];
                $warehouseId = $first['warehouse_id'];
                $maxLeadTime = max(array_column($groupItems, 'lead_time_days'));

                $po = PurchaseOrder::create([
                    'po_number' => $this->invoiceNumberService->generatePoNumber(),
                    'supplier_id' => $supplierId,
                    'warehouse_id' => $warehouseId,
                    'order_date' => now()->toDateString(),
                    'expected_delivery_date' => now()->addDays($maxLeadTime)->toDateString(),
                    'status' => 'draft',
                    'subtotal' => 0,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'grand_total' => 0,
                    'notes' => 'Generated automatically by Auto-Reorder system',
                    'created_by' => $userId,
                ]);

                $subtotal = 0;
                $defaultUnitId = Unit::value('id');

                foreach ($groupItems as $item) {
                    $product = Product::find($item['product_id']);
                    $unitId = $product?->baseUnit()?->unit_id ?? $defaultUnitId;
                    $unitPrice = (float) $item['purchase_price'];
                    $qty = (float) $item['suggested_qty'];
                    $itemSubtotal = $unitPrice * $qty;

                    PurchaseOrderItem::create([
                        'po_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'unit_id' => $unitId,
                        'quantity_ordered' => $qty,
                        'quantity_received' => 0,
                        'unit_price' => $unitPrice,
                        'discount' => 0,
                        'subtotal' => $itemSubtotal,
                        'notes' => 'Auto-reorder draft',
                    ]);

                    $subtotal += $itemSubtotal;
                }

                $po->update([
                    'subtotal' => $subtotal,
                    'grand_total' => $subtotal,
                ]);

                $createdPoCount++;
            });
        }

        return $createdPoCount;
    }
}
