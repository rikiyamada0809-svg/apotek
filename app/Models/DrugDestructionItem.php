<?php

namespace App\Models;

use Database\Factories\DrugDestructionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugDestructionItem extends Model
{
    /** @use HasFactory<DrugDestructionItemFactory> */
    use HasFactory;

    protected $fillable = [
        'destruction_id',
        'product_id',
        'batch_id',
        'quantity',
        'unit_price',
        'loss_amount',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'loss_amount' => 'decimal:2',
        ];
    }

    public function drugDestruction(): BelongsTo
    {
        return $this->belongsTo(DrugDestruction::class, 'destruction_id');
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
