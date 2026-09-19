<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Shift;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main pharmacy dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $userRole = $user->roles->first()?->name ?? 'Pemilik';

        // 1. Period filter handling
        $period = $request->input('period', 'today');
        $today = Carbon::today();

        switch ($period) {
            case 'this_week':
                $startDate = $today->copy()->startOfWeek();
                $endDate = $today->copy()->endOfWeek();
                $periodLabel = 'Minggu Ini ('.$startDate->translatedFormat('d M').' - '.$endDate->translatedFormat('d M Y').')';
                break;
            case 'this_month':
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy()->endOfMonth();
                $periodLabel = 'Bulan Ini ('.$today->translatedFormat('F Y').')';
                break;
            case 'custom':
                $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $today->copy()->startOfDay();
                $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $today->copy()->endOfDay();
                $periodLabel = 'Periode: '.$startDate->translatedFormat('d M Y').' s/d '.$endDate->translatedFormat('d M Y');
                break;
            case 'today':
            default:
                $period = 'today';
                $startDate = $today->copy()->startOfDay();
                $endDate = $today->copy()->endOfDay();
                $periodLabel = 'Hari Ini ('.$today->translatedFormat('d F Y').')';
                break;
        }

        // 2. Sales Metrics in Selected Period
        $salesQuery = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $totalSalesAmount = (float) (clone $salesQuery)->sum('grand_total');
        $totalSalesCount = (int) (clone $salesQuery)->count();
        $avgTransaction = $totalSalesCount > 0 ? ($totalSalesAmount / $totalSalesCount) : 0;

        // Compare with previous equivalent period
        $daysDiff = max(1, $startDate->diffInDays($endDate) + 1);
        $prevStartDate = $startDate->copy()->subDays($daysDiff);
        $prevEndDate = $startDate->copy()->subSecond();
        $prevSalesAmount = (float) Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$prevStartDate, $prevEndDate])
            ->sum('grand_total');
        $salesGrowthPercentage = $prevSalesAmount > 0
            ? (($totalSalesAmount - $prevSalesAmount) / $prevSalesAmount) * 100
            : 0;

        // 3. Kas & Shift Hari Ini
        $activeShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        $todayCashSales = (float) SalePayment::where('payment_method', 'cash')
            ->whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->sum('amount');

        $todayNonCashSales = (float) SalePayment::where('payment_method', '!=', 'cash')
            ->whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->sum('amount');

        // 4. Hutang Supplier & Piutang Pelanggan
        $unpaidInvoices = PurchaseInvoice::whereIn('payment_status', ['unpaid', 'partial'])->get();
        $totalSupplierDebt = (float) $unpaidInvoices->sum(fn ($inv) => $inv->remaining_amount);
        $nearDueSupplierDebt = (float) $unpaidInvoices
            ->filter(fn ($inv) => $inv->due_date && Carbon::parse($inv->due_date)->lte($today->copy()->addDays(7)))
            ->sum(fn ($inv) => $inv->remaining_amount);

        $upcomingDebtInvoices = PurchaseInvoice::with('supplier')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $totalCustomerReceivables = (float) Customer::sum('outstanding_balance');
        $debtCustomersCount = Customer::where('outstanding_balance', '>', 0)->count();

        // 5. Stock & Batch Alerts
        $allProducts = Product::with(['stocks', 'batches'])->get();
        $outOfStockCount = $allProducts->filter(fn ($p) => $p->stocks->sum('quantity') <= 0)->count();
        $lowStockCount = $allProducts->filter(function ($p) {
            $currentStock = $p->stocks->sum('quantity');
            $minStock = $p->min_stock ?? 10;

            return $currentStock > 0 && $currentStock <= $minStock;
        })->count();

        $expiredBatchesCount = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->where('expiry_date', '<', $today->toDateString())
            ->count();

        $nearExpiredBatchesCount = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [$today->toDateString(), $today->copy()->addDays(90)->toDateString()])
            ->count();

        $nearExpiredList = Batch::with(['product.category'])
            ->where('status', 'active')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [$today->toDateString(), $today->copy()->addDays(90)->toDateString()])
            ->orderBy('expiry_date', 'asc')
            ->take(5)
            ->get();

        // 6. 30-Day Sales Trend for Chart.js Line Chart
        $trendDates = [];
        $trendSales = [];
        $trendTx = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $dayStr = $day->format('Y-m-d');
            $trendDates[] = $day->format('d/m');

            $daySales = (float) Sale::where('status', 'completed')
                ->whereDate('sale_date', $dayStr)
                ->sum('grand_total');
            $dayTx = (int) Sale::where('status', 'completed')
                ->whereDate('sale_date', $dayStr)
                ->count();

            $trendSales[] = $daySales;
            $trendTx[] = $dayTx;
        }

        // 7. Top 10 Best Selling Medicines
        $topProductsQuery = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product.category')
            ->take(10)
            ->get();

        $topProductLabels = $topProductsQuery->map(fn ($it) => $it->product?->name ?? 'Produk')->toArray();
        $topProductQuantities = $topProductsQuery->map(fn ($it) => (float) $it->total_qty)->toArray();

        // 8. Payment Methods Breakdown
        $paymentMethodsQuery = SalePayment::select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();

        $paymentLabels = $paymentMethodsQuery->map(fn ($p) => ucfirst($p->payment_method))->toArray();
        $paymentAmounts = $paymentMethodsQuery->map(fn ($p) => (float) $p->total_amount)->toArray();

        // 9. Recent Sales
        $recentSales = Sale::with(['customer', 'cashier'])
            ->where('status', 'completed')
            ->latest('sale_date')
            ->take(6)
            ->get();

        // 10. Role-specific stats
        $pendingPrescriptionsCount = Prescription::where('status', 'submitted')->count();
        $validatedPrescriptionsCount = Prescription::where('status', 'validated')->count();
        $recentPrescriptions = Prescription::with(['customer', 'doctor'])
            ->latest('prescription_date')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'userRole',
            'period',
            'periodLabel',
            'startDate',
            'endDate',
            'totalSalesAmount',
            'totalSalesCount',
            'avgTransaction',
            'salesGrowthPercentage',
            'activeShift',
            'todayCashSales',
            'todayNonCashSales',
            'totalSupplierDebt',
            'nearDueSupplierDebt',
            'upcomingDebtInvoices',
            'totalCustomerReceivables',
            'debtCustomersCount',
            'outOfStockCount',
            'lowStockCount',
            'expiredBatchesCount',
            'nearExpiredBatchesCount',
            'nearExpiredList',
            'trendDates',
            'trendSales',
            'trendTx',
            'topProductsQuery',
            'topProductLabels',
            'topProductQuantities',
            'paymentLabels',
            'paymentAmounts',
            'recentSales',
            'pendingPrescriptionsCount',
            'validatedPrescriptionsCount',
            'recentPrescriptions'
        ));
    }
}
