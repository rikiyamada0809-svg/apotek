<?php

namespace Database\Factories;

use App\Models\DrugDestruction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DrugDestruction>
 */
class DrugDestructionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'destruction_number' => 'BA-MUSNAH-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'destruction_date' => now()->toDateString(),
            'witness_name' => 'apt. Budi Santoso, S.Farm',
            'witness_role' => 'Apoteker Pendamping',
            'method' => 'Insinerasi Limbah Medis',
            'status' => 'draft',
            'total_loss_amount' => 450000,
            'notes' => 'Pemusnahan obat expired triwulan 1',
            'executed_by' => User::factory(),
            'approved_by' => User::factory(),
        ];
    }
}
