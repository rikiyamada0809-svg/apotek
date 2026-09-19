<?php

namespace App\Models;

use Database\Factories\ProductUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    /** @use HasFactory<ProductUnitFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_id',
        'conversion_factor',
        'is_base_unit',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'prescription_price',
        'barcode',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:2',
            'is_base_unit' => 'boolean',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'prescription_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
