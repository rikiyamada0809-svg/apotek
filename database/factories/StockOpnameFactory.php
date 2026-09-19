<?php

namespace Database\Factories;

use App\Models\StockOpname;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockOpname>
 */
class StockOpnameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opname_number' => 'SO-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'warehouse_id' => Warehouse::factory(),
            'opname_date' => now()->toDateString(),
            'scope' => 'full',
            'freeze_stock' => false,
            'status' => 'draft',
            'total_items' => 0,
            'matched_items' => 0,
            'discrepancy_items' => 0,
            'notes' => 'Stok opname bulanan',
            'initiated_by' => User::factory(),
        ];
    }
}
