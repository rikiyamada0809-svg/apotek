<?php

namespace App\Models;

use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'supplier_id',
        'purchase_price',
        'received_qty',
        'current_qty',
        'status',
        'barcode',
        'notes',
        'received_date',
        'receipt_id',
    ];

    protected function casts(): array
    {
        return [
            'manufacturing_date' => 'date',
            'expiry_date' => 'date',
            'received_date' => 'date',
            'purchase_price' => 'decimal:2',
            'received_qty' => 'decimal:2',
            'current_qty' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function compoundItems(): HasMany
    {
        return $this->hasMany(CompoundItem::class);
    }

    public function destructionItems(): HasMany
    {
        return $this->hasMany(DrugDestructionItem::class);
    }

    public function recalls(): HasMany
    {
        return $this->hasMany(BatchRecall::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->endOfDay()->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return ! $this->isExpired() && $this->expiry_date->lessThanOrEqualTo(now()->addDays($days)->endOfDay());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('current_qty', '>', 0);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expiry_date', '<', now()->toDateString());
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('status', 'active')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function scopeFefo(Builder $query): Builder
    {
        return $query->orderBy('expiry_date', 'asc');
    }
}
