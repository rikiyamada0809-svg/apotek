<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMutation;

class MutationService
{
    /**
     * Record a stock mutation entry for auditing.
     */
    public function recordMutation(
        int $productId,
        int $warehouseId,
        string $mutationType,
        float $quantity,
        ?int $batchId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): StockMutation {
        // Find current total stock in warehouse for this product and batch
        $currentStockQuery = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($batchId !== null) {
            $currentStockQuery->where('batch_id', $batchId);
        }

        $stockAfter = (float) $currentStockQuery->sum('quantity');

        // Derive stockBefore depending on mutation direction
        if ($mutationType === 'in' || $mutationType === 'transfer_in') {
            $stockBefore = max(0, $stockAfter - $quantity);
        } elseif ($mutationType === 'out' || $mutationType === 'disposal' || $mutationType === 'expired' || $mutationType === 'transfer_out') {
            $stockBefore = $stockAfter + $quantity;
        } else {
            $stockBefore = $stockAfter;
        }

        return StockMutation::create([
            'product_id' => $productId,
            'batch_id' => $batchId,
            'warehouse_id' => $warehouseId,
            'mutation_type' => $mutationType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes,
            'created_by' => $userId,
        ]);
    }
}
