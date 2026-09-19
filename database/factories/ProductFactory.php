<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $drugClasses = ['Bebas', 'Bebas Terbatas', 'Keras', 'Narkotika', 'Psikotropika'];
        $forms = ['Tablet', 'Kapsul', 'Sirup', 'Salep', 'Krim', 'Injeksi', 'Tetes', 'Suppositoria'];
        $storageConditions = ['Suhu Kamar', 'Kulkas 2-8°C', 'Tempat Sejuk & Kering'];

        return [
            'product_code' => 'PRD-'.fake()->unique()->numerify('#####'),
            'barcode' => fake()->unique()->ean13(),
            'name' => fake()->words(3, true),
            'generic_name' => fake()->word(),
            'brand' => fake()->company(),
            'category_id' => Category::factory(),
            'drug_class' => fake()->randomElement($drugClasses),
            'form' => fake()->randomElement($forms),
            'strength' => fake()->randomElement(['500mg', '250mg', '100mg', '10mg/ml', '5%']),
            'manufacturer_id' => Manufacturer::factory(),
            'requires_prescription' => fake()->boolean(30),
            'storage_condition' => fake()->randomElement($storageConditions),
            'description' => fake()->sentence(),
            'min_order_qty' => fake()->numberBetween(1, 10),
            'is_active' => true,
            'image_url' => null,
        ];
    }

    public function prescriptionRequired(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_prescription' => true,
            'drug_class' => 'Keras',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
