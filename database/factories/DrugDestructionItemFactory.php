<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\DrugDestruction;
use App\Models\DrugDestructionItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DrugDestructionItem>
 */
class DrugDestructionItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'destruction_id' => DrugDestruction::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => 20,
            'unit_price' => 15000,
            'loss_amount' => 300000,
            'reason' => 'Kadaluarsa dan perubahan warna tablet',
            'notes' => 'Segel botol sudah terbuka',
        ];
    }
}
