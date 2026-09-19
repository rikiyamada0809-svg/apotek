<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Collection;

class StockService
{
    /**
     * Update or create a stock record for a given product and warehouse.
     */
    public function updateStock(
        int $productId,
        int $warehouseId,
        float $qtyChange,
        ?int $batchId = null,
        ?int $locationId = null
    ): Stock {
        $stock = Stock::firstOrNew([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'batch_id' => $batchId,
            'location_id' => $locationId,
        ], [
            'quantity' => 0,
            'reserved_qty' => 0,
            'available_qty' => 0,
            'status' => 'available',
        ]);

        $newQuantity = max(0, (float) $stock->quantity + $qtyChange);
        $reservedQty = (float) $stock->reserved_qty;
        $availableQty = max(0, $newQuantity - $reservedQty);

        $stock->quantity = $newQuantity;
        $stock->available_qty = $availableQty;
        $stock->status = $availableQty > 0 ? 'available' : 'out_of_stock';
        $stock->save();

        return $stock;
    }

    /**
     * Reserve stock for pending transactions (e.g. checkout / pending sale).
     */
    public function reserveStock(int $productId, int $warehouseId, float $qty, ?int $batchId = null): bool
    {
        $query = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($batchId !== null) {
            $query->where('batch_id', $batchId);
        }

        $stock = $query->first();

        if (! $stock || (float) $stock->available_qty < $qty) {
            return false;
        }

        $stock->reserved_qty = (float) $stock->reserved_qty + $qty;
        $stock->available_qty = max(0, (float) $stock->quantity - (float) $stock->reserved_qty);
        $stock->save();

        return true;
    }

    /**
     * Release previously reserved stock.
     */
    public function releaseStock(int $productId, int $warehouseId, float $qty, ?int $batchId = null): bool
    {
        $query = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($batchId !== null) {
            $query->where('batch_id', $batchId);
        }

        $stock = $query->first();

        if (! $stock) {
            return false;
        }

        $stock->reserved_qty = max(0, (float) $stock->reserved_qty - $qty);
        $stock->available_qty = max(0, (float) $stock->quantity - (float) $stock->reserved_qty);
        $stock->save();

        return true;
    }

    /**
     * Get total available stock across warehouses or for a specific warehouse.
     */
    public function getAvailableStock(int $productId, ?int $warehouseId = null): float
    {
        $query = Stock::where('product_id', $productId);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        return (float) $query->sum('available_qty');
    }

    /**
     * Get batches according to First-Expired First-Out (FEFO) principle with available stock.
     *
     * @return Collection<int, Batch>
     */
    public function getFEFOBatch(int $productId, ?int $warehouseId = null): Collection
    {
        return Batch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('current_qty', '>', 0)
            ->where('expiry_date', '>=', now()->toDateString())
            ->whereHas('stocks', function ($query) use ($warehouseId) {
                if ($warehouseId !== null) {
                    $query->where('warehouse_id', $warehouseId);
                }
                $query->where('available_qty', '>', 0);
            })
            ->with(['stocks' => function ($query) use ($warehouseId) {
                if ($warehouseId !== null) {
                    $query->where('warehouse_id', $warehouseId);
                }
            }])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }
}
