<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StorageLocation;
use App\Models\Warehouse;
use App\Services\InvoiceNumberService;
use App\Services\MutationService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected StockService $stockService,
        protected MutationService $mutationService
    ) {}

    /**
     * Display a listing of stock opnames.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $warehouseId = $request->input('warehouse_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = StockOpname::with(['warehouse', 'initiator', 'approver', 'items'])
            ->when($search, function ($q, $search) {
                $q->where('opname_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($startDate, fn ($q) => $q->whereDate('opname_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('opname_date', '<=', $endDate))
            ->latest('opname_date')
            ->latest('id');

        $opnames = $query->paginate(15)->withQueryString();

        $totalOpnames = StockOpname::count();
        $draftOpnames = StockOpname::where('status', 'draft')->count();
        $inProgressOpnames = StockOpname::whereIn('status', ['in_progress', 'submitted', 'reviewed'])->count();
        $completedOpnames = StockOpname::where('status', 'completed')->count();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('stock-opnames.index', compact(
            'opnames',
            'warehouses',
            'totalOpnames',
            'draftOpnames',
            'inProgressOpnames',
            'completedOpnames'
        ));
    }

    /**
     * Show the form for creating a new stock opname.
     */
    public function create(Request $request): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $locations = StorageLocation::with('warehouse')->where('is_active', true)->orderBy('name')->get();
        $opnameNumber = $this->invoiceNumberService->generateStockOpnameNumber();

        return view('stock-opnames.create', compact('warehouses', 'categories', 'locations', 'opnameNumber'));
    }

    /**
     * Store a newly created stock opname in storage and generate snapshot items.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'opname_date' => ['required', 'date'],
            'scope' => ['required', 'in:full,category,location'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['exists:storage_locations,id'],
            'freeze_stock' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $opnameNumber = $this->invoiceNumberService->generateStockOpnameNumber();
        $warehouseId = (int) $validated['warehouse_id'];
        $scope = $validated['scope'];
        $scopeFilter = null;

        if ($scope === 'category') {
            $scopeFilter = json_encode($validated['category_ids'] ?? []);
        } elseif ($scope === 'location') {
            $scopeFilter = json_encode($validated['location_ids'] ?? []);
        }

        $stockOpname = DB::transaction(function () use ($validated, $opnameNumber, $warehouseId, $scope, $scopeFilter) {
            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'warehouse_id' => $warehouseId,
                'opname_date' => $validated['opname_date'],
                'scope' => $scope,
                'scope_filter' => $scopeFilter,
                'freeze_stock' => ! empty($validated['freeze_stock']),
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'initiated_by' => Auth::id(),
            ]);

            // Query items to snapshot based on scope
            if ($scope === 'location') {
                $locationIds = $validated['location_ids'] ?? [];
                $stocks = Stock::where('warehouse_id', $warehouseId)
                    ->whereIn('location_id', $locationIds)
                    ->with(['product', 'batch'])
                    ->get();

                foreach ($stocks as $stock) {
                    StockOpnameItem::create([
                        'opname_id' => $opname->id,
                        'product_id' => $stock->product_id,
                        'batch_id' => $stock->batch_id,
                        'location_id' => $stock->location_id,
                        'system_qty' => (float) $stock->quantity,
                        'physical_qty' => (float) $stock->quantity,
                        'discrepancy' => 0,
                    ]);
                }
            } else {
                $productQuery = Product::active();
                if ($scope === 'category') {
                    $categoryIds = $validated['category_ids'] ?? [];
                    $productQuery->whereIn('category_id', $categoryIds);
                }
                $products = $productQuery->get();

                foreach ($products as $product) {
                    $stocks = Stock::where('warehouse_id', $warehouseId)
                        ->where('product_id', $product->id)
                        ->get();

                    if ($stocks->isNotEmpty()) {
                        foreach ($stocks as $stock) {
                            StockOpnameItem::create([
                                'opname_id' => $opname->id,
                                'product_id' => $product->id,
                                'batch_id' => $stock->batch_id,
                                'location_id' => $stock->location_id,
                                'system_qty' => (float) $stock->quantity,
                                'physical_qty' => (float) $stock->quantity,
                                'discrepancy' => 0,
                            ]);
                        }
                    } else {
                        // Include product with 0 system stock
                        StockOpnameItem::create([
                            'opname_id' => $opname->id,
                            'product_id' => $product->id,
                            'batch_id' => null,
                            'location_id' => null,
                            'system_qty' => 0,
                            'physical_qty' => 0,
                            'discrepancy' => 0,
                        ]);
                    }
                }
            }

            $opname->recalculateSummary();

            return $opname;
        });

        return redirect()->route('stock-opnames.show', $stockOpname)
            ->with('success', "Stok Opname {$stockOpname->opname_number} berhasil dibuat sebagai draft.");
    }

    /**
     * Display the specified stock opname.
     */
    public function show(StockOpname $stockOpname): View
    {
        $stockOpname->load([
            'warehouse',
            'initiator',
            'approver',
            'items.product.category',
            'items.product.productUnits.unit',
            'items.batch',
            'items.storageLocation',
            'items.counter',
        ]);

        $items = $stockOpname->items;
        $totalItems = $items->count();
        $matchedItems = $items->filter(fn ($i) => (float) $i->discrepancy == 0)->count();
        $discrepancyItems = $items->filter(fn ($i) => (float) $i->discrepancy != 0)->count();

        $deficitCount = $items->filter(fn ($i) => (float) $i->discrepancy < 0)->count();
        $surplusCount = $items->filter(fn ($i) => (float) $i->discrepancy > 0)->count();

        return view('stock-opnames.show', compact(
            'stockOpname',
            'totalItems',
            'matchedItems',
            'discrepancyItems',
            'deficitCount',
            'surplusCount'
        ));
    }

    /**
     * Start the stock opname (Draft -> In Progress).
     */
    public function start(StockOpname $stockOpname): RedirectResponse
    {
        if ($stockOpname->status !== 'draft') {
            return back()->with('error', 'Hanya stok opname berstatus draft yang dapat dimulai.');
        }

        $stockOpname->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->route('stock-opnames.count', $stockOpname)
            ->with('success', "Stok Opname {$stockOpname->opname_number} telah dimulai. Silakan lakukan perhitungan fisik.");
    }

    /**
     * Physical counting interface.
     */
    public function count(StockOpname $stockOpname): View|RedirectResponse
    {
        if (! in_array($stockOpname->status, ['in_progress', 'submitted'])) {
            return redirect()->route('stock-opnames.show', $stockOpname)
                ->with('error', 'Perhitungan fisik hanya dapat dilakukan saat opname sedang berlangsung.');
        }

        $stockOpname->load([
            'warehouse',
            'items.product.category',
            'items.product.productUnits.unit',
            'items.batch',
            'items.storageLocation',
        ]);

        return view('stock-opnames.count', compact('stockOpname'));
    }

    /**
     * Update counted physical quantities.
     */
    public function updateCount(Request $request, StockOpname $stockOpname): RedirectResponse
    {
        if (! in_array($stockOpname->status, ['in_progress', 'submitted'])) {
            return back()->with('error', 'Stok opname tidak sedang dalam tahap perhitungan.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:stock_opname_items,id'],
            'items.*.physical_qty' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'action' => ['nullable', 'string', 'in:save_draft,submit'],
        ]);

        DB::transaction(function () use ($validated, $stockOpname) {
            foreach ($validated['items'] as $itemData) {
                $item = StockOpnameItem::where('opname_id', $stockOpname->id)
                    ->where('id', $itemData['id'])
                    ->first();

                if ($item) {
                    $item->physical_qty = $itemData['physical_qty'];
                    $item->calculateDiscrepancy();
                    $item->counted_by = Auth::id();
                    $item->counted_at = now();
                    $item->notes = $itemData['notes'] ?? null;
                    $item->save();
                }
            }

            $stockOpname->recalculateSummary();

            if (($validated['action'] ?? '') === 'submit') {
                $stockOpname->update(['status' => 'submitted']);
            }
        });

        if (($validated['action'] ?? '') === 'submit') {
            return redirect()->route('stock-opnames.review', $stockOpname)
                ->with('success', 'Hasil perhitungan fisik berhasil disubmit. Silakan tinjau selisih stok.');
        }

        return back()->with('success', 'Data perhitungan fisik berhasil disimpan.');
    }

    /**
     * Submit count for manager review.
     */
    public function submit(StockOpname $stockOpname): RedirectResponse
    {
        if ($stockOpname->status !== 'in_progress') {
            return back()->with('error', 'Status opname harus in progress untuk dapat diajukan.');
        }

        $stockOpname->recalculateSummary();
        $stockOpname->update(['status' => 'submitted']);

        return redirect()->route('stock-opnames.review', $stockOpname)
            ->with('success', "Stok opname {$stockOpname->opname_number} berhasil disubmit untuk peninjauan selisih.");
    }

    /**
     * Discrepancy review interface.
     */
    public function review(StockOpname $stockOpname): View|RedirectResponse
    {
        if (! in_array($stockOpname->status, ['submitted', 'reviewed', 'approved', 'completed'])) {
            return redirect()->route('stock-opnames.show', $stockOpname)
                ->with('error', 'Peninjauan selisih hanya dapat dilakukan setelah opname disubmit.');
        }

        $stockOpname->load([
            'warehouse',
            'items' => fn ($q) => $q->where('discrepancy', '!=', 0)->with([
                'product.category',
                'product.productUnits.unit',
                'batch',
                'storageLocation',
                'counter',
            ]),
        ]);

        return view('stock-opnames.review', compact('stockOpname'));
    }

    /**
     * Save discrepancy reasons during review.
     */
    public function updateReview(Request $request, StockOpname $stockOpname): RedirectResponse
    {
        if (! in_array($stockOpname->status, ['submitted', 'reviewed'])) {
            return back()->with('error', 'Status opname tidak dapat diperbarui dalam peninjauan.');
        }

        $validated = $request->validate([
            'reasons' => ['required', 'array'],
            'reasons.*.id' => ['required', 'exists:stock_opname_items,id'],
            'reasons.*.reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $stockOpname) {
            foreach ($validated['reasons'] as $entry) {
                StockOpnameItem::where('opname_id', $stockOpname->id)
                    ->where('id', $entry['id'])
                    ->update([
                        'discrepancy_reason' => $entry['reason'],
                    ]);
            }

            $stockOpname->update(['status' => 'reviewed']);
        });

        return redirect()->route('stock-opnames.show', $stockOpname)
            ->with('success', 'Alasan selisih opname berhasil disimpan dan status diperbarui menjadi Reviewed.');
    }

    /**
     * Approve stock opname adjustment by manager.
     */
    public function approve(StockOpname $stockOpname): RedirectResponse
    {
        if (! in_array($stockOpname->status, ['submitted', 'reviewed'])) {
            return back()->with('error', 'Hanya stok opname yang telah disubmit atau direview yang dapat disetujui.');
        }

        $stockOpname->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Stok Opname {$stockOpname->opname_number} berhasil disetujui. Siap untuk penyesuaian stok.");
    }

    /**
     * Complete the stock opname and execute inventory adjustments.
     */
    public function complete(StockOpname $stockOpname): RedirectResponse
    {
        if ($stockOpname->status !== 'approved') {
            return back()->with('error', 'Stok opname harus disetujui terlebih dahulu sebelum penyesuaian stok diterapkan.');
        }

        DB::transaction(function () use ($stockOpname) {
            $warehouseId = $stockOpname->warehouse_id;

            foreach ($stockOpname->items as $item) {
                $discrepancy = (float) $item->discrepancy;

                if ($discrepancy != 0) {
                    // Update warehouse stock
                    $this->stockService->updateStock(
                        productId: $item->product_id,
                        warehouseId: $warehouseId,
                        qtyChange: $discrepancy,
                        batchId: $item->batch_id,
                        locationId: $item->location_id
                    );

                    // If linked to a batch, update batch current_qty
                    if ($item->batch_id) {
                        $batch = Batch::find($item->batch_id);
                        if ($batch) {
                            $batch->current_qty = max(0, (float) $batch->current_qty + $discrepancy);
                            $batch->save();
                        }
                    }

                    // Record stock mutation
                    $this->mutationService->recordMutation(
                        productId: $item->product_id,
                        warehouseId: $warehouseId,
                        mutationType: 'adjustment',
                        quantity: abs($discrepancy),
                        batchId: $item->batch_id,
                        referenceType: 'stock_opname',
                        referenceId: $stockOpname->id,
                        notes: "Penyesuaian Opname {$stockOpname->opname_number}: ".($item->discrepancy_reason ?: ($discrepancy > 0 ? 'Surplus fisik' : 'Defisit fisik')),
                        userId: Auth::id()
                    );
                }

                $item->adjustment_approved = true;
                $item->save();
            }

            $stockOpname->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('stock-opnames.show', $stockOpname)
            ->with('success', "Stok Opname {$stockOpname->opname_number} telah diselesaikan. Seluruh stok fisik telah disinkronkan dan mutasi stok dicatat.");
    }

    /**
     * Generate printable PDF report for stock opname.
     */
    public function printPdf(StockOpname $stockOpname): Response
    {
        $stockOpname->load([
            'warehouse',
            'initiator',
            'approver',
            'items.product.category',
            'items.product.productUnits.unit',
            'items.batch',
            'items.storageLocation',
            'items.counter',
        ]);

        $pdf = Pdf::loadView('stock-opnames.pdf', compact('stockOpname'));

        return $pdf->stream("Laporan-Stok-Opname-{$stockOpname->opname_number}.pdf");
    }
}
