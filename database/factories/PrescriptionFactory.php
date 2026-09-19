<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'prescription_number' => 'RX-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'doctor_id' => Doctor::factory(),
            'customer_id' => Customer::factory(),
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Faringitis Akut',
            'status' => 'submitted',
            'validated_by' => User::factory(),
            'validated_at' => now(),
            'notes' => 'Pasien alergi penisilin',
        ];
    }
}
