<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseReturnItem>
 */
class PurchaseReturnItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'return_id' => PurchaseReturn::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => 10,
            'unit_price' => 25000,
            'subtotal' => 250000,
            'reason' => 'Kemasan rusak',
            'notes' => 'Segel rusak dari pengiriman',
        ];
    }
}
