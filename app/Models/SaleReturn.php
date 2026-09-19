<?php

namespace App\Models;

use Database\Factories\SaleReturnFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    /** @use HasFactory<SaleReturnFactory> */
    use HasFactory;

    protected $fillable = [
        'return_number',
        'original_sale_id',
        'customer_id',
        'return_date',
        'reason',
        'status',
        'resolution_type',
        'refund_amount',
        'refund_method',
        'processed_by',
        'approved_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class, 'return_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
