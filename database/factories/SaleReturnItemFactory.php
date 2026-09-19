<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleReturnItem>
 */
class SaleReturnItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'return_id' => SaleReturn::factory(),
            'original_sale_item_id' => SaleItem::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
            'item_condition' => 'good',
            'return_to_stock' => true,
            'notes' => 'Kondisi kemasan tersegel dan baik',
        ];
    }
}
