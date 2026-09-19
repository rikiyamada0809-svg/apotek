<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    /**
     * Display a listing of batches with FEFO ordering and expiration tracking.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $productId = $request->input('product_id');
        $supplierId = $request->input('supplier_id');

        $batchesQuery = Batch::with(['product.category', 'supplier', 'stocks.warehouse', 'stocks.storageLocation'])
            ->when($search, function ($query, $search) {
                $query->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('product_code', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            })
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($status, function ($query, $status) {
                if ($status === 'expired') {
                    $query->where('expiry_date', '<', now()->toDateString());
                } elseif ($status === 'expiring_soon') {
                    $query->where('status', 'active')
                        ->where('current_qty', '>', 0)
                        ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
                } elseif ($status === 'depleted') {
                    $query->where('current_qty', '<=', 0);
                } elseif ($status === 'active') {
                    $query->where('status', 'active')
                        ->where('current_qty', '>', 0)
                        ->where('expiry_date', '>', now()->addDays(30)->toDateString());
                }
            })
            ->orderBy('expiry_date', 'asc');

        $batches = $batchesQuery->paginate(15)->withQueryString();

        // Calculate summary counters
        $totalBatches = Batch::count();
        $activeBatches = Batch::where('status', 'active')->where('current_qty', '>', 0)->where('expiry_date', '>=', now()->toDateString())->count();
        $nearExpiryBatches = Batch::where('status', 'active')->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->count();
        $expiredBatches = Batch::where('current_qty', '>', 0)->where('expiry_date', '<', now()->toDateString())->count();

        $products = Product::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('batches.index', compact(
            'batches',
            'products',
            'suppliers',
            'totalBatches',
            'activeBatches',
            'nearExpiryBatches',
            'expiredBatches'
        ));
    }

    /**
     * Display detailed batch information, storage locations, and its mutation history.
     */
    public function show(Batch $batch): View
    {
        $batch->load([
            'product.category',
            'product.manufacturer',
            'product.productUnits.unit',
            'supplier',
            'stocks.warehouse',
            'stocks.storageLocation',
            'stockMutations' => function ($query) {
                $query->with(['creator', 'warehouse'])->latest();
            },
        ]);

        return view('batches.show', compact('batch'));
    }
}
