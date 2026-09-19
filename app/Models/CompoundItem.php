<?php

namespace App\Models;

use Database\Factories\CompoundItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompoundItem extends Model
{
    /** @use HasFactory<CompoundItemFactory> */
    use HasFactory;

    protected $fillable = [
        'compound_id',
        'product_id',
        'batch_id',
        'quantity_used',
        'unit_id',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity_used' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function compound(): BelongsTo
    {
        return $this->belongsTo(Compound::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
