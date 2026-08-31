<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 200),
            'stock' => fake()->numberBetween(0, 200),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'image_url' => fake()->imageUrl(),
            'is_active' => true,
        ];
    }
}
