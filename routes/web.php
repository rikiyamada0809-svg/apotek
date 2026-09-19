<?php

use App\Http\Controllers\AbcAnalysisController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchRecallController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompoundController;
use App\Http\Controllers\CompoundTemplateController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DrugDestructionController;
use App\Http\Controllers\ExpiredController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\Reports\FinanceReportController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\SalesReportController;
use App\Http\Controllers\Reports\StockReportController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockReorderController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StorageLocationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');
    Route::resource('manufacturers', ManufacturerController::class)->except('show');
    Route::resource('suppliers', SupplierController::class);
    Route::resource('products', ProductController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('doctors', DoctorController::class)->except('show');
    Route::resource('warehouses', WarehouseController::class);

    // Storage Locations (nested under warehouses)
    Route::get('warehouses/{warehouse}/locations/create', [StorageLocationController::class, 'create'])->name('storage-locations.create');
    Route::post('warehouses/{warehouse}/locations', [StorageLocationController::class, 'store'])->name('storage-locations.store');
    Route::get('warehouses/{warehouse}/locations/{location}/edit', [StorageLocationController::class, 'edit'])->name('storage-locations.edit');
    Route::put('warehouses/{warehouse}/locations/{location}', [StorageLocationController::class, 'update'])->name('storage-locations.update');
    Route::delete('warehouses/{warehouse}/locations/{location}', [StorageLocationController::class, 'destroy'])->name('storage-locations.destroy');

    // User Management
    Route::resource('users', UserController::class)->except('show');
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // Modul Pembelian (Purchasing)
    Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    Route::get('purchase-orders/{purchase_order}/pdf', [PurchaseOrderController::class, 'printPdf'])->name('purchase-orders.print-pdf');
    Route::resource('purchase-orders', PurchaseOrderController::class);

    Route::get('goods-receipts/{goods_receipt}/pdf', [GoodsReceiptController::class, 'printPdf'])->name('goods-receipts.print-pdf');
    Route::resource('goods-receipts', GoodsReceiptController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('purchase-invoices', PurchaseInvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('purchase-invoices/{purchase_invoice}/payments', [PurchasePaymentController::class, 'store'])->name('purchase-invoices.payments.store');

    // Retur Pembelian
    Route::post('purchase-returns/{purchase_return}/approve', [PurchaseReturnController::class, 'approve'])->name('purchase-returns.approve');
    Route::post('purchase-returns/{purchase_return}/send', [PurchaseReturnController::class, 'send'])->name('purchase-returns.send');
    Route::post('purchase-returns/{purchase_return}/resolve', [PurchaseReturnController::class, 'resolve'])->name('purchase-returns.resolve');
    Route::get('purchase-returns/{purchase_return}/pdf', [PurchaseReturnController::class, 'printPdf'])->name('purchase-returns.print-pdf');
    Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store', 'show']);

    // Shift Kasir
    Route::get('shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
    Route::post('shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::post('shifts/{shift}/expense', [ShiftController::class, 'expense'])->name('shifts.expense');

    // Point of Sale (POS)
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/search-products', [PosController::class, 'searchProducts'])->name('pos.search-products');
    Route::post('pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('pos/history', [PosController::class, 'history'])->name('pos.history');
    Route::post('pos/{sale}/void', [PosController::class, 'void'])->name('pos.void');
    Route::get('pos/{sale}/receipt', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('pos/{sale}/receipt/pdf', [PosController::class, 'receiptPdf'])->name('pos.receipt.pdf');

    // Retur Penjualan
    Route::post('sale-returns/{sale_return}/approve', [SaleReturnController::class, 'approve'])->name('sale-returns.approve');
    Route::post('sale-returns/{sale_return}/complete', [SaleReturnController::class, 'complete'])->name('sale-returns.complete');
    Route::get('sale-returns/{sale_return}/pdf', [SaleReturnController::class, 'printPdf'])->name('sale-returns.print-pdf');
    Route::resource('sale-returns', SaleReturnController::class)->only(['index', 'create', 'store', 'show']);

    // Inventori & Manajemen Stok
    Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('stocks/card', [StockController::class, 'card'])->name('stocks.card');
    Route::post('stocks/{product}/settings', [StockController::class, 'updateSettings'])->name('stocks.settings.update');
    Route::resource('batches', BatchController::class)->only(['index', 'show']);

    // Auto-Reorder & Rekomendasi Pemesanan
    Route::get('stocks/reorder', [StockReorderController::class, 'index'])->name('stocks.reorder');
    Route::post('stocks/reorder/generate-po', [StockReorderController::class, 'generatePo'])->name('stocks.reorder.generate-po');

    // Analisis ABC (Pareto Inventory)
    Route::get('stocks/abc', [AbcAnalysisController::class, 'index'])->name('stocks.abc');
    Route::post('stocks/abc/run', [AbcAnalysisController::class, 'runAnalysis'])->name('stocks.abc.run');

    // Transfer Stok Multi-Gudang
    Route::get('stock-transfers/api/warehouse-stock', [StockTransferController::class, 'getWarehouseStock'])->name('stock-transfers.warehouse-stock');
    Route::post('stock-transfers/{stock_transfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve');
    Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');
    Route::post('stock-transfers/{stock_transfer}/cancel', [StockTransferController::class, 'cancel'])->name('stock-transfers.cancel');
    Route::get('stock-transfers/{stock_transfer}/pdf', [StockTransferController::class, 'printPdf'])->name('stock-transfers.pdf');
    Route::resource('stock-transfers', StockTransferController::class);

    // Stok Opname
    Route::post('stock-opnames/{stock_opname}/start', [StockOpnameController::class, 'start'])->name('stock-opnames.start');
    Route::get('stock-opnames/{stock_opname}/count', [StockOpnameController::class, 'count'])->name('stock-opnames.count');
    Route::post('stock-opnames/{stock_opname}/count', [StockOpnameController::class, 'updateCount'])->name('stock-opnames.count.update');
    Route::post('stock-opnames/{stock_opname}/submit', [StockOpnameController::class, 'submit'])->name('stock-opnames.submit');
    Route::get('stock-opnames/{stock_opname}/review', [StockOpnameController::class, 'review'])->name('stock-opnames.review');
    Route::post('stock-opnames/{stock_opname}/review', [StockOpnameController::class, 'updateReview'])->name('stock-opnames.review.update');
    Route::post('stock-opnames/{stock_opname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve');
    Route::post('stock-opnames/{stock_opname}/complete', [StockOpnameController::class, 'complete'])->name('stock-opnames.complete');
    Route::get('stock-opnames/{stock_opname}/pdf', [StockOpnameController::class, 'printPdf'])->name('stock-opnames.print-pdf');
    Route::resource('stock-opnames', StockOpnameController::class)->only(['index', 'create', 'store', 'show']);

    // Monitoring Data Expired
    Route::get('expired/dashboard', [ExpiredController::class, 'dashboard'])->name('expired.dashboard');
    Route::get('expired', [ExpiredController::class, 'index'])->name('expired.index');
    Route::post('expired/run-check', [ExpiredController::class, 'runCheck'])->name('expired.run-check');

    // Pemusnahan Obat
    Route::post('drug-destructions/{drug_destruction}/approve', [DrugDestructionController::class, 'approve'])->name('drug-destructions.approve');
    Route::post('drug-destructions/{drug_destruction}/complete', [DrugDestructionController::class, 'complete'])->name('drug-destructions.complete');
    Route::post('drug-destructions/{drug_destruction}/upload-document', [DrugDestructionController::class, 'uploadDocument'])->name('drug-destructions.upload-document');
    Route::get('drug-destructions/{drug_destruction}/pdf', [DrugDestructionController::class, 'printPdf'])->name('drug-destructions.pdf');
    Route::resource('drug-destructions', DrugDestructionController::class)->only(['index', 'create', 'store', 'show']);

    // Batch Recall (Penarikan Obat)
    Route::post('batch-recalls/{batch_recall}/update-progress', [BatchRecallController::class, 'updateProgress'])->name('batch-recalls.update-progress');
    Route::post('batch-recalls/{batch_recall}/complete', [BatchRecallController::class, 'complete'])->name('batch-recalls.complete');
    Route::get('batch-recalls/{batch_recall}/pdf', [BatchRecallController::class, 'printPdf'])->name('batch-recalls.pdf');
    Route::resource('batch-recalls', BatchRecallController::class)->only(['index', 'create', 'store', 'show']);

    // Resep Dokter & Salinan Resep
    Route::post('prescriptions/{prescription}/validate', [PrescriptionController::class, 'validatePrescription'])->name('prescriptions.validate');
    Route::post('prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
    Route::post('prescriptions/{prescription}/copy', [PrescriptionController::class, 'copyPrescription'])->name('prescriptions.copy');
    Route::resource('prescriptions', PrescriptionController::class)->only(['index', 'create', 'store', 'show']);

    // Template Racikan & Peracikan Obat
    Route::resource('compound-templates', CompoundTemplateController::class);
    Route::get('compounds/{compound}/label', [CompoundController::class, 'printLabel'])->name('compounds.label');
    Route::resource('compounds', CompoundController::class)->only(['create', 'store', 'show']);

    // Pusat Laporan Apotek
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Laporan Penjualan
    Route::get('reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('reports/sales/pdf', [SalesReportController::class, 'exportPdf'])->name('reports.sales.pdf');
    Route::get('reports/sales/csv', [SalesReportController::class, 'exportCsv'])->name('reports.sales.csv');

    // Laporan Stok & Gudang
    Route::get('reports/stock', [StockReportController::class, 'index'])->name('reports.stock');
    Route::get('reports/stock/pdf', [StockReportController::class, 'exportPdf'])->name('reports.stock.pdf');
    Route::get('reports/stock/csv', [StockReportController::class, 'exportCsv'])->name('reports.stock.csv');

    // Laporan Keuangan & Pembelian
    Route::get('reports/finance', [FinanceReportController::class, 'index'])->name('reports.finance');
    Route::get('reports/finance/pdf', [FinanceReportController::class, 'exportPdf'])->name('reports.finance.pdf');
    Route::get('reports/finance/csv', [FinanceReportController::class, 'exportCsv'])->name('reports.finance.csv');
});

require __DIR__.'/auth.php';
