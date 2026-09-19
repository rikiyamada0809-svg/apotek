<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DrugDestruction;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    /**
     * Display purchasing & finance report.
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'purchases');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        $supplierId = $request->input('supplier_id');

        $data = $this->getReportData($type, $startDate, $endDate, $supplierId);

        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.finance', array_merge([
            'type' => $type,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'supplierId' => $supplierId,
            'suppliers' => $suppliers,
        ], $data));
    }

    /**
     * Export finance report as PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $type = $request->input('type', 'purchases');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

        $data = $this->getReportData($type, $startDate, $endDate);

        $reportTitle = 'Laporan Keuangan & Pembelian - '.match ($type) {
            'debts' => 'Daftar Hutang Supplier',
            'aging_debts' => 'Analisis Umur Hutang (Aging)',
            'receivables' => 'Piutang Pelanggan',
            'profit_loss' => 'Laba Rugi Sederhana',
            'cash_flow' => 'Arus Kas Masuk & Keluar',
            default => 'Faktur Pembelian Supplier',
        };

        $pdf = Pdf::loadView('reports.pdf', array_merge([
            'reportCategory' => 'Keuangan & Pembelian',
            'reportTitle' => $reportTitle,
            'startDate' => $startDate->format('d/m/Y'),
            'endDate' => $endDate->format('d/m/Y'),
            'type' => $type,
        ], $data));

        return $pdf->stream("Laporan-Keuangan-{$type}-".date('Ymd').'.pdf');
    }

    /**
     * Export finance report as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'purchases');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

        $data = $this->getReportData($type, $startDate, $endDate);
        $filename = "laporan-keuangan-{$type}-".date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($type, $data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            if ($type === 'debts' || $type === 'aging_debts') {
                fputcsv($handle, ['No. Faktur', 'Supplier', 'Tgl Faktur', 'Jatuh Tempo', 'Total Tagihan (Rp)', 'Sudah Dibayar (Rp)', 'Sisa Hutang (Rp)', 'Status']);
                foreach ($data['items'] as $inv) {
                    fputcsv($handle, [
                        $inv->invoice_number,
                        $inv->supplier?->name ?? 'Supplier',
                        $inv->invoice_date?->format('d/m/Y'),
                        $inv->due_date?->format('d/m/Y'),
                        $inv->total_amount,
                        $inv->paid_amount,
                        $inv->remaining_amount,
                        $inv->payment_status,
                    ]);
                }
            } elseif ($type === 'profit_loss') {
                fputcsv($handle, ['Pos Laporan', 'Nilai (Rp)']);
                fputcsv($handle, ['Total Pendapatan Penjualan', $data['totalRevenue']]);
                fputcsv($handle, ['Total Beban Pokok Penjualan (HPP)', $data['totalHpp']]);
                fputcsv($handle, ['Laba Kotor Usaha', $data['grossProfit']]);
                fputcsv($handle, ['Beban Kerugian Pemusnahan Obat', $data['destructionLoss']]);
                fputcsv($handle, ['Laba Bersih Operasional Sederhana', $data['netIncome']]);
            } else {
                fputcsv($handle, ['No. Faktur', 'Supplier', 'Tanggal', 'Status Bayar', 'Total Pembelian (Rp)']);
                foreach ($data['items'] as $inv) {
                    fputcsv($handle, [
                        $inv->invoice_number,
                        $inv->supplier?->name ?? '-',
                        $inv->invoice_date?->format('d/m/Y'),
                        $inv->payment_status,
                        $inv->total_amount,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Helper to query finance data.
     */
    protected function getReportData(string $type, Carbon $startDate, Carbon $endDate, ?int $supplierId = null): array
    {
        switch ($type) {
            case 'debts':
                $items = PurchaseInvoice::whereIn('payment_status', ['unpaid', 'partial'])
                    ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                    ->with('supplier')
                    ->orderBy('due_date', 'asc')
                    ->get();

                $totalDebt = (float) $items->sum(fn ($i) => $i->remaining_amount);

                return compact('items', 'totalDebt');

            case 'aging_debts':
                $now = Carbon::today();
                $invoices = PurchaseInvoice::whereIn('payment_status', ['unpaid', 'partial'])
                    ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                    ->with('supplier')
                    ->get();

                $agingSummary = [
                    'current' => 0,      // Belum jatuh tempo
                    'days_1_30' => 0,    // 1 - 30 hari lewat
                    'days_31_60' => 0,   // 31 - 60 hari lewat
                    'days_61_90' => 0,   // 61 - 90 hari lewat
                    'days_over_90' => 0, // > 90 hari lewat
                ];

                foreach ($invoices as $inv) {
                    $rem = (float) $inv->remaining_amount;
                    if (! $inv->due_date || Carbon::parse($inv->due_date)->gt($now)) {
                        $agingSummary['current'] += $rem;
                        $inv->aging_category = 'Belum Jatuh Tempo';
                    } else {
                        $daysOverdue = (int) Carbon::parse($inv->due_date)->diffInDays($now);
                        if ($daysOverdue <= 30) {
                            $agingSummary['days_1_30'] += $rem;
                            $inv->aging_category = '1 - 30 Hari';
                        } elseif ($daysOverdue <= 60) {
                            $agingSummary['days_31_60'] += $rem;
                            $inv->aging_category = '31 - 60 Hari';
                        } elseif ($daysOverdue <= 90) {
                            $agingSummary['days_61_90'] += $rem;
                            $inv->aging_category = '61 - 90 Hari';
                        } else {
                            $agingSummary['days_over_90'] += $rem;
                            $inv->aging_category = '> 90 Hari';
                        }
                    }
                }

                $items = $invoices;
                $totalDebt = array_sum($agingSummary);

                return compact('items', 'agingSummary', 'totalDebt');

            case 'receivables':
                $items = Customer::where('outstanding_balance', '>', 0)
                    ->orderByDesc('outstanding_balance')
                    ->get();

                $totalReceivables = (float) $items->sum('outstanding_balance');

                return compact('items', 'totalReceivables');

            case 'profit_loss':
                $totalRevenue = (float) Sale::where('status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->sum('grand_total');

                $items = SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')
                        ->whereBetween('sale_date', [$startDate, $endDate]);
                })->with(['batch', 'product'])->get();

                $totalHpp = 0;
                foreach ($items as $it) {
                    $unitCost = (float) ($it->batch?->purchase_price ?? $it->product?->purchase_price ?? 0);
                    $totalHpp += ($unitCost * (float) $it->quantity);
                }

                $grossProfit = $totalRevenue - $totalHpp;

                $destructionLoss = (float) DrugDestruction::where('status', 'completed')
                    ->whereBetween('destruction_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('total_loss_amount');

                $netIncome = $grossProfit - $destructionLoss;

                return compact('totalRevenue', 'totalHpp', 'grossProfit', 'destructionLoss', 'netIncome');

            case 'cash_flow':
                // Cash Inflows: Sales Payments
                $cashSales = (float) SalePayment::where('payment_method', 'cash')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');
                $digitalSales = (float) SalePayment::where('payment_method', '!=', 'cash')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');
                $totalInflow = $cashSales + $digitalSales;

                // Cash Outflows: Purchase Payments
                $purchasePayments = (float) PurchasePayment::whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('amount');
                $totalOutflow = $purchasePayments;

                $netCashFlow = $totalInflow - $totalOutflow;

                return compact('cashSales', 'digitalSales', 'totalInflow', 'purchasePayments', 'totalOutflow', 'netCashFlow');

            case 'purchases':
            default:
                $items = PurchaseInvoice::whereDate('invoice_date', '>=', $startDate->toDateString())
                    ->whereDate('invoice_date', '<=', $endDate->toDateString())
                    ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                    ->with('supplier')
                    ->latest('invoice_date')
                    ->get();

                $totalPurchases = (float) $items->sum('total_amount');
                $totalPaid = (float) $items->sum('paid_amount');
                $totalUnpaid = $totalPurchases - $totalPaid;

                return compact('items', 'totalPurchases', 'totalPaid', 'totalUnpaid');
        }
    }
}
