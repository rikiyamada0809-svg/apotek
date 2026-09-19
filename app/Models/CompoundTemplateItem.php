<?php

namespace App\Models;

use Database\Factories\CompoundTemplateItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompoundTemplateItem extends Model
{
    /** @use HasFactory<CompoundTemplateItemFactory> */
    use HasFactory;

    protected $fillable = [
        'template_id',
        'product_id',
        'quantity_per_unit',
        'unit_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_per_unit' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CompoundTemplate::class, 'template_id');
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
