<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'product_code',
        'barcode',
        'name',
        'generic_name',
        'brand',
        'category_id',
        'drug_class',
        'form',
        'strength',
        'manufacturer_id',
        'requires_prescription',
        'storage_condition',
        'description',
        'min_order_qty',
        'is_active',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
            'min_order_qty' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function baseUnit(): ?ProductUnit
    {
        return $this->productUnits()->where('is_base_unit', true)->first();
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockSettings(): HasMany
    {
        return $this->hasMany(StockSetting::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function totalAvailableStock(): float
    {
        return (float) $this->stocks()->sum('available_qty');
    }

    public function getPurchasePriceAttribute(): float
    {
        return (float) ($this->baseUnit()?->purchase_price ?? $this->batches()->latest()->value('purchase_price') ?? 0);
    }

    public function getSellingPriceAttribute(): float
    {
        return (float) ($this->baseUnit()?->selling_price ?? 0);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePrescription(Builder $query): Builder
    {
        return $query->where('requires_prescription', true);
    }
}
