<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\StockSetting;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AbcAnalysisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:abc-analysis {--days=90 : Period in days to analyze sales data} {--warehouse= : Specific warehouse ID to update settings for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform ABC Pareto inventory classification based on sales revenue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 90;
        }

        $warehouseId = $this->option('warehouse');
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $this->info("Running ABC Analysis based on sales data from the last {$days} days...");

        // Calculate sales revenue per product
        $salesData = SaleItem::select('sale_items.product_id', DB::raw('SUM(sale_items.subtotal) as total_revenue'), DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->where('sales.sale_date', '>=', $startDate)
            ->groupBy('sale_items.product_id')
            ->pluck('total_revenue', 'product_id')
            ->toArray();

        // Get all active products
        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->warn('No active products found.');

            return self::SUCCESS;
        }

        // Map revenues and sort descending
        $productRevenues = [];
        $grandTotalRevenue = 0.0;

        foreach ($products as $product) {
            $rev = isset($salesData[$product->id]) ? (float) $salesData[$product->id] : 0.0;
            $productRevenues[] = [
                'product' => $product,
                'revenue' => $rev,
            ];
            $grandTotalRevenue += $rev;
        }

        usort($productRevenues, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);

        $categoryCounts = ['A' => 0, 'B' => 0, 'C' => 0];
        $categoryRevenue = ['A' => 0.0, 'B' => 0.0, 'C' => 0.0];

        $runningTotal = 0.0;
        $classifiedProducts = [];

        foreach ($productRevenues as $item) {
            $runningTotal += $item['revenue'];
            $cumulativePct = $grandTotalRevenue > 0 ? ($runningTotal / $grandTotalRevenue) * 100 : 100;

            if ($grandTotalRevenue == 0) {
                // If no sales at all, classify as C
                $category = 'C';
            } elseif ($cumulativePct <= 70.0 || (empty($classifiedProducts) && $item['revenue'] > 0)) {
                $category = 'A';
            } elseif ($cumulativePct <= 90.0) {
                $category = 'B';
            } else {
                $category = 'C';
            }

            $categoryCounts[$category]++;
            $categoryRevenue[$category] += $item['revenue'];

            $classifiedProducts[] = [
                'product_id' => $item['product']->id,
                'product_code' => $item['product']->product_code,
                'product_name' => $item['product']->name,
                'revenue' => $item['revenue'],
                'cumulative_pct' => round($cumulativePct, 2),
                'category' => $category,
            ];
        }

        // Target warehouses to update stock_settings
        $warehouses = Warehouse::query();
        if ($warehouseId) {
            $warehouses->where('id', $warehouseId);
        }
        $targetWarehouses = $warehouses->get();

        if ($targetWarehouses->isEmpty()) {
            $defaultWarehouse = Warehouse::first();
            if ($defaultWarehouse) {
                $targetWarehouses = collect([$defaultWarehouse]);
            }
        }

        // Update database stock_settings
        DB::transaction(function () use ($classifiedProducts, $targetWarehouses) {
            foreach ($classifiedProducts as $item) {
                foreach ($targetWarehouses as $wh) {
                    StockSetting::updateOrCreate(
                        [
                            'product_id' => $item['product_id'],
                            'warehouse_id' => $wh->id,
                        ],
                        [
                            'abc_category' => $item['category'],
                        ]
                    );
                }
            }
        });

        $this->info('ABC Analysis complete! Stock settings updated.');
        $this->table(
            ['Category', 'Description', 'Item Count', '% of Items', 'Total Revenue', '% of Revenue'],
            [
                [
                    'Category A',
                    'High Value (Top 70% Revenue)',
                    $categoryCounts['A'],
                    round(($categoryCounts['A'] / count($products)) * 100, 1).'%',
                    'Rp '.number_format($categoryRevenue['A'], 0, ',', '.'),
                    $grandTotalRevenue > 0 ? round(($categoryRevenue['A'] / $grandTotalRevenue) * 100, 1).'%' : '0%',
                ],
                [
                    'Category B',
                    'Medium Value (Next 20% Revenue)',
                    $categoryCounts['B'],
                    round(($categoryCounts['B'] / count($products)) * 100, 1).'%',
                    'Rp '.number_format($categoryRevenue['B'], 0, ',', '.'),
                    $grandTotalRevenue > 0 ? round(($categoryRevenue['B'] / $grandTotalRevenue) * 100, 1).'%' : '0%',
                ],
                [
                    'Category C',
                    'Low Value (Bottom 10% Revenue)',
                    $categoryCounts['C'],
                    round(($categoryCounts['C'] / count($products)) * 100, 1).'%',
                    'Rp '.number_format($categoryRevenue['C'], 0, ',', '.'),
                    $grandTotalRevenue > 0 ? round(($categoryRevenue['C'] / $grandTotalRevenue) * 100, 1).'%' : '0%',
                ],
            ]
        );

        return self::SUCCESS;
    }
}
