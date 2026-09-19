<?php

namespace Database\Factories;

use App\Models\StorageLocation;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorageLocation>
 */
class StorageLocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'code' => 'RAK-'.fake()->unique()->bothify('?#-##'),
            'name' => 'Rak '.fake()->bothify('?#'),
            'type' => 'rak',
            'temperature_range' => null,
            'is_active' => true,
        ];
    }

    public function kulkas(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'kulkas',
            'temperature_range' => '2-8°C',
        ]);
    }

    public function lemariNarkotika(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'lemari_narkotika',
        ]);
    }
}
