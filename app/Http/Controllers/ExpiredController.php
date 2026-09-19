<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ExpiredController extends Controller
{
    /**
     * Dashboard for expired items and alert level monitoring.
     */
    public function dashboard(): View
    {
        $today = now()->toDateString();
        $in30Days = now()->addDays(30)->toDateString();
        $in90Days = now()->addDays(90)->toDateString();
        $in180Days = now()->addDays(180)->toDateString();

        // Expired Batches (already past expiry date or marked expired)
        $expiredQuery = Batch::where(function ($q) use ($today) {
            $q->where('status', 'expired')
                ->orWhere('expiry_date', '<', $today);
        })->where('current_qty', '>', 0);

        $expiredCount = (clone $expiredQuery)->count();
        $expiredTotalQty = (clone $expiredQuery)->sum('current_qty');
        $expiredEstimatedLoss = (clone $expiredQuery)->get()->sum(fn ($b) => (float) $b->current_qty * (float) $b->purchase_price);

        // Critical (1 - 30 days)
        $criticalQuery = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [$today, $in30Days]);
        $criticalCount = (clone $criticalQuery)->count();
        $criticalEstimatedValue = (clone $criticalQuery)->get()->sum(fn ($b) => (float) $b->current_qty * (float) $b->purchase_price);

        // Warning (31 - 90 days)
        $warningQuery = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [now()->addDays(31)->toDateString(), $in90Days]);
        $warningCount = (clone $warningQuery)->count();

        // Caution (91 - 180 days)
        $cautionQuery = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [now()->addDays(91)->toDateString(), $in180Days]);
        $cautionCount = (clone $cautionQuery)->count();

        // Normal (> 180 days)
        $normalCount = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->where('expiry_date', '>', $in180Days)
            ->count();

        // Urgent upcoming batches (critical + top expired)
        $urgentBatches = Batch::with(['product.category', 'supplier'])
            ->where('current_qty', '>', 0)
            ->where(function ($q) use ($in30Days) {
                $q->where('status', 'expired')
                    ->orWhere('expiry_date', '<=', $in30Days);
            })
            ->orderBy('expiry_date', 'asc')
            ->take(10)
            ->get();

        // Expiring by category (critical + warning)
        $categories = Category::withCount(['products as expiring_batches_count' => function ($q) use ($in90Days) {
            $q->whereHas('batches', function ($bq) use ($in90Days) {
                $bq->where('current_qty', '>', 0)
                    ->where('expiry_date', '<=', $in90Days);
            });
        }])->get()->filter(fn ($cat) => $cat->expiring_batches_count > 0)->take(6);

        return view('expired.dashboard', compact(
            'expiredCount',
            'expiredTotalQty',
            'expiredEstimatedLoss',
            'criticalCount',
            'criticalEstimatedValue',
            'warningCount',
            'cautionCount',
            'normalCount',
            'urgentBatches',
            'categories'
        ));
    }

    /**
     * List of expired and near-expired batches with multiple filter ranges.
     */
    public function index(Request $request): View
    {
        $range = $request->input('range', 'all'); // '1_month', '3_months', '6_months', 'expired', 'all'
        $search = $request->input('search');
        $supplierId = $request->input('supplier_id');
        $categoryId = $request->input('category_id');

        $today = now()->toDateString();
        $in30Days = now()->addDays(30)->toDateString();
        $in90Days = now()->addDays(90)->toDateString();
        $in180Days = now()->addDays(180)->toDateString();

        $query = Batch::with(['product.category', 'product.productUnits.unit', 'supplier', 'stocks.warehouse'])
            ->where('current_qty', '>', 0);

        // Apply range filter
        if ($range === '1_month') {
            $query->where('status', 'active')
                ->whereBetween('expiry_date', [$today, $in30Days]);
        } elseif ($range === '3_months') {
            $query->where('status', 'active')
                ->whereBetween('expiry_date', [$today, $in90Days]);
        } elseif ($range === '6_months') {
            $query->where('status', 'active')
                ->whereBetween('expiry_date', [$today, $in180Days]);
        } elseif ($range === 'expired') {
            $query->where(function ($q) use ($today) {
                $q->where('status', 'expired')
                    ->orWhere('expiry_date', '<', $today);
            });
        }

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('product_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($categoryId) {
            $query->whereHas('product', fn ($pq) => $pq->where('category_id', $categoryId));
        }

        $batches = $query->orderBy('expiry_date', 'asc')->paginate(20)->withQueryString();

        // Calculate counts for quick tab badges
        $tabCounts = [
            'all' => Batch::where('current_qty', '>', 0)->count(),
            'expired' => Batch::where('current_qty', '>', 0)->where(function ($q) use ($today) {
                $q->where('status', 'expired')->orWhere('expiry_date', '<', $today);
            })->count(),
            '1_month' => Batch::where('status', 'active')->where('current_qty', '>', 0)->whereBetween('expiry_date', [$today, $in30Days])->count(),
            '3_months' => Batch::where('status', 'active')->where('current_qty', '>', 0)->whereBetween('expiry_date', [$today, $in90Days])->count(),
            '6_months' => Batch::where('status', 'active')->where('current_qty', '>', 0)->whereBetween('expiry_date', [$today, $in180Days])->count(),
        ];

        return view('expired.index', compact('batches', 'range', 'tabCounts'));
    }

    /**
     * Manually trigger scan and status update for expired batches.
     */
    public function runCheck(): RedirectResponse
    {
        Artisan::call('expired:check');

        return back()->with('success', 'Sinkronisasi status batch kedaluwarsa berhasil dieksekusi.');
    }
}
