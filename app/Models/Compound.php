<?php

namespace App\Models;

use Database\Factories\CompoundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compound extends Model
{
    /** @use HasFactory<CompoundFactory> */
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'template_id',
        'compound_number',
        'name',
        'total_packages',
        'service_fee',
        'total_material_cost',
        'total_price',
        'prepared_by',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'total_packages' => 'integer',
            'service_fee' => 'decimal:2',
            'total_material_cost' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CompoundTemplate::class, 'template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CompoundItem::class);
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function calculateTotals(): void
    {
        $this->total_material_cost = $this->items->sum('subtotal');
        $this->total_price = (float) $this->total_material_cost + (float) $this->service_fee;
    }
}
