<?php

namespace App\Models;

use Database\Factories\BatchRecallFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchRecall extends Model
{
    /** @use HasFactory<BatchRecallFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'recall_number',
        'recall_source',
        'recall_reason',
        'recall_date',
        'affected_qty',
        'sold_qty',
        'recovered_qty',
        'status',
        'action_taken',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'recall_date' => 'date',
            'affected_qty' => 'decimal:2',
            'sold_qty' => 'decimal:2',
            'recovered_qty' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getRecoveryPercentageAttribute(): float
    {
        if ((float) $this->sold_qty <= 0) {
            return 100.0;
        }

        return min(100.0, round(((float) $this->recovered_qty / (float) $this->sold_qty) * 100, 1));
    }
}
