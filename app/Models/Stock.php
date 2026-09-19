<?php

namespace App\Models;

use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    /** @use HasFactory<StockFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_id',
        'warehouse_id',
        'location_id',
        'quantity',
        'reserved_qty',
        'available_qty',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'reserved_qty' => 'decimal:2',
            'available_qty' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function storageLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'location_id');
    }

    public function recalculateAvailable(): void
    {
        $this->available_qty = max(0, (float) $this->quantity - (float) $this->reserved_qty);
        if ($this->available_qty <= 0) {
            $this->status = 'out_of_stock';
        } else {
            $this->status = 'available';
        }
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('available_qty', '>', 0);
    }

    public function scopeInWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }
}
