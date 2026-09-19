<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            if (Schema::hasTable('products') && Schema::hasTable('batches') && Schema::hasTable('stocks')) {
                $today = now()->toDateString();
                $expiringBatchesCount = Batch::where('status', 'active')
                    ->where('current_qty', '>', 0)
                    ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
                    ->count();

                $expiredBatchesCount = Batch::where('current_qty', '>', 0)
                    ->where(function ($q) use ($today) {
                        $q->where('status', 'expired')
                            ->orWhere('expiry_date', '<', $today);
                    })
                    ->count();

                $allProducts = Product::with(['stocks', 'stockSettings'])->get();
                $lowStockCount = $allProducts->filter(function ($prod) {
                    $avail = (float) $prod->stocks->sum('available_qty');
                    $setting = $prod->stockSettings->first();
                    $min = $setting ? (float) $setting->min_stock : 10;

                    return $avail <= $min;
                })->count();

                $view->with([
                    'sidebarLowStockCount' => $lowStockCount,
                    'sidebarExpiringBatchesCount' => $expiringBatchesCount,
                    'sidebarExpiredCount' => $expiredBatchesCount,
                ]);
            } else {
                $view->with([
                    'sidebarLowStockCount' => 0,
                    'sidebarExpiringBatchesCount' => 0,
                    'sidebarExpiredCount' => 0,
                ]);
            }
        });
    }
}
