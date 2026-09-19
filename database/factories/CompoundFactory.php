<?php

namespace Database\Factories;

use App\Models\Compound;
use App\Models\CompoundTemplate;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Compound>
 */
class CompoundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'template_id' => CompoundTemplate::factory(),
            'compound_number' => 'CMP-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => 'Racikan Puyer Anak Spesial',
            'total_packages' => 10,
            'service_fee' => 5000,
            'total_material_cost' => 15000,
            'total_price' => 20000,
            'prepared_by' => User::factory(),
            'verified_by' => User::factory(),
        ];
    }
}
