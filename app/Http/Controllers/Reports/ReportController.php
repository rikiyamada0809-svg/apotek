<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the central reports hub.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $thisMonthStart = $today->copy()->startOfMonth();
        $thisMonthEnd = $today->copy()->endOfMonth();

        // Quick overview metrics for the current month
        $monthlyRevenue = (float) Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$thisMonthStart, $thisMonthEnd])
            ->sum('grand_total');

        $monthlyTransactions = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();

        $totalUnpaidDebt = (float) PurchaseInvoice::whereIn('payment_status', ['unpaid', 'partial'])
            ->get()
            ->sum(fn ($inv) => $inv->remaining_amount);

        $totalReceivables = (float) Customer::sum('outstanding_balance');

        return view('reports.index', compact(
            'monthlyRevenue',
            'monthlyTransactions',
            'totalProducts',
            'totalCustomers',
            'totalSuppliers',
            'totalUnpaidDebt',
            'totalReceivables'
        ));
    }
}
