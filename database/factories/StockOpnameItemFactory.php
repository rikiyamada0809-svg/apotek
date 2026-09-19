<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockOpnameItem>
 */
class StockOpnameItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opname_id' => StockOpname::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'location_id' => StorageLocation::factory(),
            'system_qty' => 100,
            'physical_qty' => 98,
            'discrepancy' => -2,
            'discrepancy_reason' => 'Selisih fisik 2 tablet',
            'adjustment_approved' => false,
            'counted_by' => User::factory(),
            'counted_at' => now(),
            'notes' => 'Hitung fisik di rak',
        ];
    }
}
