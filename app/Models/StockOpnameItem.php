<?php

namespace App\Models;

use Database\Factories\StockOpnameItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    /** @use HasFactory<StockOpnameItemFactory> */
    use HasFactory;

    protected $fillable = [
        'opname_id',
        'product_id',
        'batch_id',
        'location_id',
        'system_qty',
        'physical_qty',
        'discrepancy',
        'discrepancy_reason',
        'adjustment_approved',
        'counted_by',
        'counted_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_qty' => 'decimal:2',
            'physical_qty' => 'decimal:2',
            'discrepancy' => 'decimal:2',
            'adjustment_approved' => 'boolean',
            'counted_at' => 'datetime',
        ];
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'opname_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function storageLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'location_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function calculateDiscrepancy(): void
    {
        $this->discrepancy = (float) $this->physical_qty - (float) $this->system_qty;
    }

    public function hasDiscrepancy(): bool
    {
        return (float) $this->discrepancy != 0;
    }

    public function isDeficit(): bool
    {
        return (float) $this->discrepancy < 0;
    }

    public function isSurplus(): bool
    {
        return (float) $this->discrepancy > 0;
    }
}
