<?php

namespace App\Models;

use Database\Factories\SaleReturnItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    /** @use HasFactory<SaleReturnItemFactory> */
    use HasFactory;

    protected $fillable = [
        'return_id',
        'original_sale_item_id',
        'product_id',
        'batch_id',
        'quantity',
        'unit_price',
        'subtotal',
        'item_condition',
        'return_to_stock',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'return_to_stock' => 'boolean',
        ];
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'return_id');
    }

    public function originalSaleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class, 'original_sale_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
