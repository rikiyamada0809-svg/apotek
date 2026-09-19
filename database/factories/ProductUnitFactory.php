<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    public function definition(): array
    {
        $purchasePrice = fake()->numberBetween(1000, 100000);

        return [
            'product_id' => Product::factory(),
            'unit_id' => Unit::factory(),
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => $purchasePrice,
            'selling_price' => $purchasePrice * 1.25,
            'wholesale_price' => $purchasePrice * 1.15,
            'prescription_price' => $purchasePrice * 1.30,
            'barcode' => null,
        ];
    }

    public function baseUnit(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_base_unit' => true,
            'conversion_factor' => 1,
        ]);
    }
}
