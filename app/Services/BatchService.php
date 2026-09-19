<?php

namespace App\Services;

use App\Models\Batch;

class BatchService
{
    /**
     * Create a new batch record.
     *
     * @param  array<string, mixed>  $data
     */
    public function createBatch(array $data): Batch
    {
        $status = 'active';
        if (isset($data['expiry_date']) && now()->parse($data['expiry_date'])->endOfDay()->isPast()) {
            $status = 'expired';
        }

        return Batch::create(array_merge([
            'status' => $status,
            'current_qty' => $data['received_qty'] ?? 0,
        ], $data));
    }

    /**
     * Update current quantity and status of a batch.
     */
    public function updateBatchQty(Batch $batch, float $qtyChange): Batch
    {
        $newQty = max(0, (float) $batch->current_qty + $qtyChange);
        $status = $batch->status;

        if ($batch->isExpired()) {
            $status = 'expired';
        } elseif ($newQty <= 0) {
            $status = 'exhausted';
        } else {
            $status = 'active';
        }

        $batch->update([
            'current_qty' => $newQty,
            'status' => $status,
        ]);

        return $batch;
    }

    /**
     * Check if a batch is expired and update its status if needed.
     */
    public function checkExpiry(Batch $batch): string
    {
        if ($batch->isExpired()) {
            if ($batch->status !== 'expired') {
                $batch->update(['status' => 'expired']);
            }

            return 'expired';
        }

        if ($batch->isExpiringSoon(30)) {
            return 'expiring_soon';
        }

        return $batch->status;
    }
}
