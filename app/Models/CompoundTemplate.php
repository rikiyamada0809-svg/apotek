<?php

namespace App\Models;

use Database\Factories\CompoundTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompoundTemplate extends Model
{
    /** @use HasFactory<CompoundTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'preparation_instructions',
        'service_fee',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'service_fee' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CompoundTemplateItem::class, 'template_id');
    }

    public function compounds(): HasMany
    {
        return $this->hasMany(Compound::class, 'template_id');
    }
}
