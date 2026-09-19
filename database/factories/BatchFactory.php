<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    public function definition(): array
    {
        $mfgDate = fake()->dateTimeBetween('-1 year', '-1 month');
        $expDate = (clone $mfgDate)->modify('+2 years');

        return [
            'product_id' => Product::factory(),
            'batch_number' => 'BATCH-'.strtoupper(fake()->bothify('??###?')),
            'manufacturing_date' => $mfgDate->format('Y-m-d'),
            'expiry_date' => $expDate->format('Y-m-d'),
            'supplier_id' => Supplier::factory(),
            'purchase_price' => fake()->randomFloat(2, 5000, 100000),
            'received_qty' => 100,
            'current_qty' => 100,
            'status' => 'active',
            'barcode' => fake()->ean13(),
            'notes' => fake()->sentence(),
            'received_date' => $mfgDate->format('Y-m-d'),
            'receipt_id' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => now()->subDays(10)->toDateString(),
            'status' => 'expired',
        ]);
    }

    public function expiringSoon(int $days = 15): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => now()->addDays($days)->toDateString(),
            'status' => 'active',
        ]);
    }
}
