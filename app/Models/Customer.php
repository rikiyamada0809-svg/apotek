<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'gender',
        'customer_type',
        'membership_number',
        'membership_valid_until',
        'discount_percentage',
        'credit_limit',
        'outstanding_balance',
        'allergy_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'membership_valid_until' => 'date',
            'discount_percentage' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function isMember(): bool
    {
        return $this->customer_type === 'member'
            && ($this->membership_valid_until === null || $this->membership_valid_until->isFuture());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function scopeMembers(Builder $query): Builder
    {
        return $query->where('customer_type', 'member');
    }
}
