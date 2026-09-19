<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    /**
     * Display sales report with various sub-types.
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'daily');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        $cashierId = $request->input('cashier_id');
        $customerId = $request->input('customer_id');
        $categoryId = $request->input('category_id');

        $data = $this->getReportData($type, $startDate, $endDate, $cashierId, $customerId, $categoryId);

        $cashiers = User::role(['Kasir', 'Apoteker', 'Pemilik'])->get();
        $customers = Customer::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('reports.sales', array_merge([
            'type' => $type,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'cashierId' => $cashierId,
            'customerId' => $customerId,
            'categoryId' => $categoryId,
            'cashiers' => $cashiers,
            'customers' => $customers,
            'categories' => $categories,
        ], $data));
    }

    /**
     * Export sales report as PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $type = $request->input('type', 'daily');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

        $data = $this->getReportData($type, $startDate, $endDate);

        $reportTitle = 'Laporan Penjualan - '.match ($type) {
            'product' => 'Per Produk',
            'customer' => 'Per Pelanggan',
            'prescription' => 'Resep Dokter',
            'gross_profit' => 'Laba Kotor (Gross Profit)',
            'payment_method' => 'Metode Pembayaran',
            'returns' => 'Retur Penjualan',
            default => 'Harian & Transaksi',
        };

        $pdf = Pdf::loadView('reports.pdf', array_merge([
            'reportCategory' => 'Penjualan',
            'reportTitle' => $reportTitle,
            'startDate' => $startDate->format('d/m/Y'),
            'endDate' => $endDate->format('d/m/Y'),
            'type' => $type,
        ], $data));

        return $pdf->stream("Laporan-Penjualan-{$type}-{$startDate->format('Ymd')}.pdf");
    }

    /**
     * Export sales report as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'daily');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

        $data = $this->getReportData($type, $startDate, $endDate);

        $filename = "laporan-penjualan-{$type}-".date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($type, $data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            if ($type === 'product') {
                fputcsv($handle, ['Nama Produk', 'Kategori', 'Qty Terjual', 'Total Omzet (Rp)', 'Rata-rata Harga (Rp)']);
                foreach ($data['items'] as $item) {
                    fputcsv($handle, [
                        $item->product?->name ?? 'Produk',
                        $item->product?->category?->name ?? '-',
                        $item->total_qty,
                        $item->total_revenue,
                        $item->total_qty > 0 ? round($item->total_revenue / $item->total_qty) : 0,
                    ]);
                }
            } elseif ($type === 'gross_profit') {
                fputcsv($handle, ['No. Faktur', 'Tanggal', 'Nama Produk', 'Qty', 'Harga Jual (Rp)', 'Harga Beli/HPP (Rp)', 'Total Laba Kotor (Rp)', 'Margin (%)']);
                foreach ($data['items'] as $item) {
                    $salePrice = (float) $item->unit_price;
                    $purchasePrice = (float) ($item->batch?->purchase_price ?? $item->product?->purchase_price ?? 0);
                    $profit = ($salePrice - $purchasePrice) * $item->quantity;
                    $margin = $salePrice > 0 ? round((($salePrice - $purchasePrice) / $salePrice) * 100, 1) : 0;

                    fputcsv($handle, [
                        $item->sale?->invoice_number,
                        $item->sale?->sale_date?->format('d/m/Y H:i'),
                        $item->product?->name,
                        $item->quantity,
                        $salePrice,
                        $purchasePrice,
                        $profit,
                        $margin.'%',
                    ]);
                }
            } else { // default daily
                fputcsv($handle, ['No. Nota', 'Tanggal & Waktu', 'Pelanggan', 'Kasir', 'Metode Bayar', 'Status', 'Total Penjualan (Rp)']);
                foreach ($data['items'] as $sale) {
                    fputcsv($handle, [
                        $sale->invoice_number,
                        $sale->sale_date?->format('d/m/Y H:i'),
                        $sale->customer?->name ?? 'Umum',
                        $sale->cashier?->name ?? '-',
                        $sale->payments->pluck('payment_method')->join(', '),
                        $sale->status,
                        $sale->grand_total,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Query data helper according to report sub-type.
     */
    protected function getReportData(string $type, Carbon $startDate, Carbon $endDate, ?int $cashierId = null, ?int $customerId = null, ?int $categoryId = null): array
    {
        switch ($type) {
            case 'product':
                $query = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
                    ->whereHas('sale', function ($q) use ($startDate, $endDate, $cashierId, $customerId) {
                        $q->where('status', 'completed')
                            ->whereBetween('sale_date', [$startDate, $endDate])
                            ->when($cashierId, fn ($sq) => $sq->where('cashier_id', $cashierId))
                            ->when($customerId, fn ($sq) => $sq->where('customer_id', $customerId));
                    })
                    ->when($categoryId, function ($q) use ($categoryId) {
                        $q->whereHas('product', fn ($pq) => $pq->where('category_id', $categoryId));
                    })
                    ->groupBy('product_id')
                    ->orderByDesc('total_revenue')
                    ->with('product.category');

                $items = $query->get();
                $totalRevenue = (float) $items->sum('total_revenue');
                $totalQty = (float) $items->sum('total_qty');

                return compact('items', 'totalRevenue', 'totalQty');

            case 'customer':
                $items = Sale::where('status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->whereNotNull('customer_id')
                    ->select('customer_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(grand_total) as total_spent'))
                    ->groupBy('customer_id')
                    ->orderByDesc('total_spent')
                    ->with('customer')
                    ->get();

                $totalRevenue = (float) $items->sum('total_spent');
                $totalOrders = (int) $items->sum('total_orders');

                return compact('items', 'totalRevenue', 'totalOrders');

            case 'prescription':
                $items = Sale::where('status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->whereNotNull('prescription_id')
                    ->with(['customer', 'cashier', 'prescription.doctor', 'items.product'])
                    ->latest('sale_date')
                    ->get();

                $totalRevenue = (float) $items->sum('grand_total');
                $totalCount = $items->count();

                return compact('items', 'totalRevenue', 'totalCount');

            case 'gross_profit':
                $items = SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')
                        ->whereBetween('sale_date', [$startDate, $endDate]);
                })
                    ->with(['product', 'batch', 'sale'])
                    ->latest('id')
                    ->get();

                $totalSales = 0;
                $totalHpp = 0;
                $totalGrossProfit = 0;

                foreach ($items as $it) {
                    $revenue = (float) $it->subtotal;
                    $unitHpp = (float) ($it->batch?->purchase_price ?? $it->product?->purchase_price ?? 0);
                    $hpp = $unitHpp * (float) $it->quantity;
                    $profit = $revenue - $hpp;

                    $totalSales += $revenue;
                    $totalHpp += $hpp;
                    $totalGrossProfit += $profit;
                }

                $grossMarginPercentage = $totalSales > 0 ? ($totalGrossProfit / $totalSales) * 100 : 0;

                return compact('items', 'totalSales', 'totalHpp', 'totalGrossProfit', 'grossMarginPercentage');

            case 'payment_method':
                $items = SalePayment::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->groupBy('payment_method')
                    ->orderByDesc('total_amount')
                    ->get();

                $totalAmount = (float) $items->sum('total_amount');

                return compact('items', 'totalAmount');

            case 'returns':
                $items = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
                    ->with(['sale.customer', 'approver', 'items.product'])
                    ->latest('return_date')
                    ->get();

                $totalRefund = (float) $items->where('status', 'completed')->sum('refund_amount');

                return compact('items', 'totalRefund');

            case 'daily':
            default:
                $items = Sale::where('status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->when($cashierId, fn ($q) => $q->where('cashier_id', $cashierId))
                    ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
                    ->with(['customer', 'cashier', 'payments'])
                    ->latest('sale_date')
                    ->get();

                $totalRevenue = (float) $items->sum('grand_total');
                $totalDiscount = (float) $items->sum('discount_amount');
                $totalTax = (float) $items->sum('tax_amount');
                $totalCount = $items->count();

                return compact('items', 'totalRevenue', 'totalDiscount', 'totalTax', 'totalCount');
        }
    }
}
