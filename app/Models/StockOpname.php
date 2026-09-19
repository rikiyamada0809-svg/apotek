<?php

namespace App\Models;

use Database\Factories\StockOpnameFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    /** @use HasFactory<StockOpnameFactory> */
    use HasFactory;

    protected $fillable = [
        'opname_number',
        'warehouse_id',
        'opname_date',
        'scope',
        'scope_filter',
        'freeze_stock',
        'status',
        'total_items',
        'matched_items',
        'discrepancy_items',
        'notes',
        'initiated_by',
        'approved_by',
        'approved_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'opname_date' => 'date',
            'freeze_stock' => 'boolean',
            'approved_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_items' => 'integer',
            'matched_items' => 'integer',
            'discrepancy_items' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class, 'opname_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public static function isWarehouseFrozen(int $warehouseId): bool
    {
        return static::where('warehouse_id', $warehouseId)
            ->where('freeze_stock', true)
            ->whereIn('status', ['in_progress', 'submitted', 'reviewed'])
            ->exists();
    }

    public function recalculateSummary(): void
    {
        $total = $this->items()->count();
        $discrepancies = $this->items()->where('discrepancy', '!=', 0)->count();
        $matched = $total - $discrepancies;

        $this->update([
            'total_items' => $total,
            'matched_items' => max(0, $matched),
            'discrepancy_items' => $discrepancies,
        ]);
    }
}
