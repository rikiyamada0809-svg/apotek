<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleReturn>
 */
class SaleReturnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'return_number' => 'RTS-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'original_sale_id' => Sale::factory(),
            'customer_id' => Customer::factory(),
            'return_date' => now()->toDateString(),
            'reason' => 'Pasien salah beli dosis obat',
            'status' => 'draft',
            'resolution_type' => 'refund',
            'refund_amount' => 100000,
            'refund_method' => 'cash',
            'processed_by' => User::factory(),
            'notes' => 'Retur dari kasir POS',
        ];
    }
}
