<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Category;
use App\Models\DrugDestruction;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StockOpname;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockReportController extends Controller
{
    /**
     * Display stock & inventory report with various sub-types.
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'current');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        $warehouseId = $request->input('warehouse_id');
        $categoryId = $request->input('category_id');

        $data = $this->getReportData($type, $startDate, $endDate, $warehouseId, $categoryId);

        $warehouses = Warehouse::where('is_active', true)->get();
        $categories = Category::orderBy('name')->get();

        return view('reports.stock', array_merge([
            'type' => $type,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'warehouseId' => $warehouseId,
            'categoryId' => $categoryId,
            'warehouses' => $warehouses,
            'categories' => $categories,
        ], $data));
    }

    /**
     * Export stock report as PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $type = $request->input('type', 'current');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

        $data = $this->getReportData($type, $startDate, $endDate);

        $reportTitle = 'Laporan Stok & Gudang - '.match ($type) {
            'mutation' => 'Mutasi & Pergerakan Stok',
            'min_stock' => 'Stok Minimum & Reorder Point',
            'expired' => 'Obat Kadaluarsa & Dekat Expired',
            'dead_stock' => 'Dead Stock / Obat Lambat Terjual',
            'opname' => 'Rekapitulasi Stok Opname',
            'destruction' => 'Pemusnahan Obat & Kerugian',
            default => 'Stok Terkini & Valuasi Persediaan',
        };

        $pdf = Pdf::loadView('reports.pdf', array_merge([
            'reportCategory' => 'Stok & Gudang',
            'reportTitle' => $reportTitle,
            'startDate' => $startDate->format('d/m/Y'),
            'endDate' => $endDate->format('d/m/Y'),
            'type' => $type,
        ], $data));

        return $pdf->stream("Laporan-Stok-{$type}-".date('Ymd').'.pdf');
    }

    /**
     * Export stock report as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'current');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

        $data = $this->getReportData($type, $startDate, $endDate);
        $filename = "laporan-stok-{$type}-".date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($type, $data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            if ($type === 'mutation') {
                fputcsv($handle, ['Tanggal', 'Produk', 'Gudang', 'Tipe Mutasi', 'Qty', 'Referensi', 'Catatan']);
                foreach ($data['items'] as $m) {
                    fputcsv($handle, [
                        $m->created_at?->format('d/m/Y H:i'),
                        $m->product?->name,
                        $m->warehouse?->name,
                        $m->mutation_type,
                        $m->quantity,
                        $m->reference_type ? ($m->reference_type.' #'.$m->reference_id) : '-',
                        $m->notes,
                    ]);
                }
            } elseif ($type === 'expired') {
                fputcsv($handle, ['Nama Produk', 'No. Batch', 'Tgl Expired', 'Sisa Qty', 'Harga Beli (Rp)', 'Nilai Kerugian (Rp)', 'Status']);
                foreach ($data['items'] as $b) {
                    fputcsv($handle, [
                        $b->product?->name,
                        $b->batch_number,
                        $b->expiry_date,
                        $b->current_qty,
                        $b->purchase_price,
                        $b->current_qty * (float) $b->purchase_price,
                        Carbon::parse($b->expiry_date)->isPast() ? 'Expired' : 'Near-Expired',
                    ]);
                }
            } else {
                fputcsv($handle, ['Nama Produk', 'Kategori', 'Total Stok', 'Harga Beli (Rp)', 'Harga Jual (Rp)', 'Total Nilai Aset (Rp)']);
                foreach ($data['items'] as $p) {
                    $totalStock = $p->stocks->sum('quantity');
                    $valuation = $totalStock * (float) $p->purchase_price;
                    fputcsv($handle, [
                        $p->name,
                        $p->category?->name ?? '-',
                        $totalStock,
                        $p->purchase_price,
                        $p->selling_price,
                        $valuation,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Helper to query data according to stock report type.
     */
    protected function getReportData(string $type, Carbon $startDate, Carbon $endDate, ?int $warehouseId = null, ?int $categoryId = null): array
    {
        switch ($type) {
            case 'mutation':
                $items = StockMutation::whereBetween('created_at', [$startDate, $endDate])
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->with(['product.category', 'warehouse', 'batch', 'user'])
                    ->latest('id')
                    ->get();

                $totalMutations = $items->count();

                return compact('items', 'totalMutations');

            case 'min_stock':
                $items = Product::with(['category', 'stocks.warehouse'])
                    ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
                    ->get()
                    ->filter(function ($p) {
                        $stock = $p->stocks->sum('quantity');
                        $min = $p->min_stock ?? 10;

                        return $stock <= $min;
                    });

                return ['items' => $items, 'totalUnderMin' => $items->count()];

            case 'expired':
                $items = Batch::with(['product.category'])
                    ->where('current_qty', '>', 0)
                    ->whereBetween('expiry_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                $totalExpiredValue = (float) $items->sum(fn ($b) => $b->current_qty * (float) $b->purchase_price);

                return compact('items', 'totalExpiredValue');

            case 'dead_stock':
                // Products that haven't been sold in the last 60 days but have stock > 0
                $thresholdDate = Carbon::today()->subDays(60);
                $items = Product::whereHas('stocks', fn ($q) => $q->where('quantity', '>', 0))
                    ->whereDoesntHave('saleItems', function ($q) use ($thresholdDate) {
                        $q->whereHas('sale', fn ($sq) => $sq->where('status', 'completed')->where('sale_date', '>=', $thresholdDate));
                    })
                    ->with(['category', 'stocks'])
                    ->get();

                $totalDeadStockValue = (float) $items->sum(fn ($p) => $p->stocks->sum('quantity') * (float) $p->purchase_price);

                return compact('items', 'totalDeadStockValue');

            case 'opname':
                $items = StockOpname::whereBetween('opname_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->with(['warehouse', 'initiator', 'approver', 'items'])
                    ->latest('opname_date')
                    ->get();

                $totalDiscrepancyLoss = (float) $items->sum(fn ($op) => abs((float) $op->total_difference_amount));

                return compact('items', 'totalDiscrepancyLoss');

            case 'destruction':
                $items = DrugDestruction::whereBetween('destruction_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->with(['executor', 'approver', 'items.product', 'items.batch'])
                    ->latest('destruction_date')
                    ->get();

                $totalDestructionLoss = (float) $items->sum('total_loss_amount');

                return compact('items', 'totalDestructionLoss');

            case 'current':
            default:
                $query = Product::with(['category', 'stocks.warehouse', 'productUnits.unit'])
                    ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId));

                $items = $query->get();

                $totalValuation = 0;
                $totalStockUnits = 0;

                foreach ($items as $product) {
                    $qty = $warehouseId
                        ? $product->stocks->where('warehouse_id', $warehouseId)->sum('quantity')
                        : $product->stocks->sum('quantity');
                    $product->filtered_stock = $qty;
                    $valuation = $qty * (float) $product->purchase_price;
                    $product->stock_valuation = $valuation;

                    $totalValuation += $valuation;
                    $totalStockUnits += $qty;
                }

                return compact('items', 'totalValuation', 'totalStockUnits');
        }
    }
}
