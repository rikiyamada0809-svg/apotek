<?php

namespace App\Models;

use Database\Factories\PrescriptionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    /** @use HasFactory<PrescriptionItemFactory> */
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'product_id',
        'dosage',
        'frequency',
        'duration_days',
        'quantity',
        'unit_id',
        'instructions',
        'is_compounded',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'duration_days' => 'integer',
            'is_compounded' => 'boolean',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
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
