<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseReturn>
 */
class PurchaseReturnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'return_number' => 'RTP-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'po_id' => PurchaseOrder::factory(),
            'supplier_id' => Supplier::factory(),
            'return_date' => now()->toDateString(),
            'reason' => 'Barang rusak / mendekati expired',
            'status' => 'draft',
            'resolution_type' => 'replacement',
            'total_amount' => 500000,
            'notes' => 'Retur barang ke supplier PBF',
            'created_by' => User::factory(),
        ];
    }
}
