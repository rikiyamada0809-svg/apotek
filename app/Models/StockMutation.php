<?php

namespace App\Models;

use Database\Factories\StockMutationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    /** @use HasFactory<StockMutationFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_id',
        'warehouse_id',
        'mutation_type',
        'reference_type',
        'reference_id',
        'quantity',
        'stock_before',
        'stock_after',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'stock_before' => 'decimal:2',
            'stock_after' => 'decimal:2',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('mutation_type', $type);
    }
}
