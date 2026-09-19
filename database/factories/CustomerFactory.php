<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'CST-'.fake()->unique()->numerify('####'),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years'),
            'gender' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'customer_type' => 'regular',
            'membership_number' => null,
            'membership_valid_until' => null,
            'discount_percentage' => 0,
            'credit_limit' => 0,
            'outstanding_balance' => 0,
            'allergy_notes' => null,
            'is_active' => true,
        ];
    }

    public function member(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_type' => 'member',
            'membership_number' => 'MBR-'.fake()->numerify('######'),
            'membership_valid_until' => now()->addYear(),
            'discount_percentage' => 5,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
