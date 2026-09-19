<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockSetting;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockSetting>
 */
class StockSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'min_stock' => 10,
            'max_stock' => 500,
            'reorder_point' => 50,
            'safety_stock' => 20,
            'lead_time_days' => 3,
            'abc_category' => 'A',
        ];
    }
}
