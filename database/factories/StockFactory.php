<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StorageLocation;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'warehouse_id' => Warehouse::factory(),
            'location_id' => StorageLocation::factory(),
            'quantity' => 100,
            'reserved_qty' => 0,
            'available_qty' => 100,
            'status' => 'available',
        ];
    }
}
