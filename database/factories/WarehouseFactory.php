<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Gudang '.fake()->unique()->city(),
            'address' => fake()->address(),
            'type' => 'main',
            'is_active' => true,
        ];
    }

    public function branch(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'branch',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
