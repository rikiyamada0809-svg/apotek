<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        $specializations = ['Umum', 'Anak', 'Kandungan', 'Jantung', 'Kulit', 'THT', 'Mata', 'Gigi', 'Saraf', 'Penyakit Dalam'];

        return [
            'name' => 'dr. '.fake()->name(),
            'sip_number' => 'SIP-'.fake()->unique()->numerify('######'),
            'specialization' => fake()->randomElement($specializations),
            'phone' => fake()->phoneNumber(),
            'clinic_name' => 'Klinik '.fake()->lastName(),
            'clinic_address' => fake()->address(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
