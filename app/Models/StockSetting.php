<?php

namespace App\Models;

use Database\Factories\StockSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockSetting extends Model
{
    /** @use HasFactory<StockSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'min_stock',
        'max_stock',
        'reorder_point',
        'safety_stock',
        'lead_time_days',
        'abc_category',
    ];

    protected function casts(): array
    {
        return [
            'min_stock' => 'decimal:2',
            'max_stock' => 'decimal:2',
            'reorder_point' => 'decimal:2',
            'safety_stock' => 'decimal:2',
            'lead_time_days' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
