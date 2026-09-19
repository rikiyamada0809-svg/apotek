<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_id',
        'product_id',
        'unit_id',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'discount',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'decimal:2',
            'quantity_received' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class, 'po_item_id');
    }

    public function getOutstandingQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity_ordered - (float) $this->quantity_received);
    }
}
