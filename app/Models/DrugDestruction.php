<?php

namespace App\Models;

use Database\Factories\DrugDestructionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrugDestruction extends Model
{
    /** @use HasFactory<DrugDestructionFactory> */
    use HasFactory;

    protected $fillable = [
        'destruction_number',
        'destruction_date',
        'witness_name',
        'witness_role',
        'method',
        'status',
        'total_loss_amount',
        'document_path',
        'notes',
        'executed_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'destruction_date' => 'date',
            'total_loss_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(DrugDestructionItem::class, 'destruction_id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
