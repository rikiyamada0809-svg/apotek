<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMutation;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMutation>
 */
class StockMutationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'warehouse_id' => Warehouse::factory(),
            'mutation_type' => 'in',
            'reference_type' => 'goods_receipt',
            'reference_id' => fake()->randomNumber(4),
            'quantity' => 100,
            'stock_before' => 0,
            'stock_after' => 100,
            'notes' => 'Penerimaan barang dari supplier',
            'created_by' => User::factory(),
        ];
    }
}
