<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AbcAnalysisController extends Controller
{
    /**
     * Display the ABC Pareto inventory analysis dashboard.
     */
    public function index(Request $request): View
    {
        $days = (int) $request->input('days', 90);
        if ($days <= 0) {
            $days = 90;
        }

        $categoryFilter = $request->input('category', 'all');
        $search = $request->input('search');
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // 1. Calculate sales revenue & quantity per product
        $salesData = SaleItem::select(
            'sale_items.product_id',
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('SUM(sale_items.quantity) as total_qty')
        )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->where('sales.sale_date', '>=', $startDate)
            ->groupBy('sale_items.product_id')
            ->get()
            ->keyBy('product_id');

        // 2. Fetch all active products
        $products = Product::where('is_active', true)
            ->with(['category', 'productUnits.unit', 'stocks'])
            ->get();

        $productAnalysis = [];
        $grandTotalRevenue = 0.0;
        $totalItemsCount = $products->count();

        foreach ($products as $product) {
            $saleInfo = $salesData->get($product->id);
            $rev = $saleInfo ? (float) $saleInfo->total_revenue : 0.0;
            $qty = $saleInfo ? (float) $saleInfo->total_qty : 0.0;

            $productAnalysis[] = [
                'product' => $product,
                'revenue' => $rev,
                'sales_qty' => $qty,
                'current_stock' => (float) $product->stocks->sum('available_qty'),
            ];
            $grandTotalRevenue += $rev;
        }

        // 3. Sort descending by revenue
        usort($productAnalysis, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);

        // 4. Calculate cumulative percentage and assign ABC class
        $categorySummary = [
            'A' => ['count' => 0, 'revenue' => 0.0, 'items' => []],
            'B' => ['count' => 0, 'revenue' => 0.0, 'items' => []],
            'C' => ['count' => 0, 'revenue' => 0.0, 'items' => []],
        ];

        $runningTotal = 0.0;
        $classifiedList = [];
        $paretoLabels = [];
        $paretoRevenues = [];
        $paretoPercentages = [];

        foreach ($productAnalysis as $index => $item) {
            $runningTotal += $item['revenue'];
            $cumulativePct = $grandTotalRevenue > 0 ? ($runningTotal / $grandTotalRevenue) * 100 : 100;

            if ($grandTotalRevenue == 0) {
                $category = 'C';
            } elseif ($cumulativePct <= 70.0 || ($index === 0 && $item['revenue'] > 0)) {
                $category = 'A';
            } elseif ($cumulativePct <= 90.0) {
                $category = 'B';
            } else {
                $category = 'C';
            }

            $categorySummary[$category]['count']++;
            $categorySummary[$category]['revenue'] += $item['revenue'];

            $dataItem = (object) [
                'product_id' => $item['product']->id,
                'product_code' => $item['product']->product_code,
                'product_name' => $item['product']->name,
                'category_name' => $item['product']->category?->name ?? '-',
                'unit_name' => $item['product']->baseUnit()?->unit?->name ?? 'Pcs',
                'current_stock' => $item['current_stock'],
                'sales_qty' => $item['sales_qty'],
                'revenue' => $item['revenue'],
                'cumulative_pct' => round($cumulativePct, 2),
                'abc_category' => $category,
            ];

            $classifiedList[] = $dataItem;

            // Chart data for top 25 products
            if ($index < 25) {
                $paretoLabels[] = $item['product']->name;
                $paretoRevenues[] = round($item['revenue'], 2);
                $paretoPercentages[] = round($cumulativePct, 2);
            }
        }

        // Filter list for display
        $filteredList = collect($classifiedList);
        if ($categoryFilter !== 'all') {
            $filteredList = $filteredList->where('abc_category', $categoryFilter);
        }

        if ($search) {
            $searchLower = strtolower($search);
            $filteredList = $filteredList->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item->product_name), $searchLower)
                    || str_contains(strtolower($item->product_code), $searchLower);
            });
        }

        $chartData = [
            'labels' => $paretoLabels,
            'revenues' => $paretoRevenues,
            'percentages' => $paretoPercentages,
        ];

        return view('stocks.abc', compact(
            'filteredList',
            'categorySummary',
            'grandTotalRevenue',
            'totalItemsCount',
            'chartData',
            'days',
            'categoryFilter',
            'search'
        ));
    }

    /**
     * Run the ABC analysis artisan command to persist category updates to database.
     */
    public function runAnalysis(Request $request): RedirectResponse
    {
        $days = (int) $request->input('days', 90);
        if ($days <= 0) {
            $days = 90;
        }

        Artisan::call('stock:abc-analysis', [
            '--days' => $days,
        ]);

        return back()->with('success', "Klasifikasi Pareto ABC berhasil dijalankan dan disimpan ke database untuk periode {$days} hari terakhir.");
    }
}
