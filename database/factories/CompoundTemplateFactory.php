<?php

namespace Database\Factories;

use App\Models\CompoundTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompoundTemplate>
 */
class CompoundTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Puyer Batuk Pilek Anak',
            'description' => 'Racikan puyer untuk anak usia 2-5 tahun',
            'preparation_instructions' => 'Gerus halus campuran obat, bagi menjadi 10 bungkus sama rata',
            'service_fee' => 5000,
            'created_by' => User::factory(),
        ];
    }
}
