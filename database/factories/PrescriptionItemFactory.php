<?php

namespace Database\Factories;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionItem>
 */
class PrescriptionItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'product_id' => Product::factory(),
            'dosage' => '500mg',
            'frequency' => '3x1 sehari',
            'duration_days' => 5,
            'quantity' => 15,
            'unit_id' => Unit::factory(),
            'instructions' => 'Diminum sesudah makan, habiskan',
            'is_compounded' => false,
            'notes' => 'Obat antibiotik',
        ];
    }
}
