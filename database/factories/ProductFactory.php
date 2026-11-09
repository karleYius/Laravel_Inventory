<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::title(fake()->unique()->words(3, true)),
            'qty' => fake()->numberBetween(0, 250),
            'price' => fake()->randomFloat(2, 4.99, 999.99),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Product that is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['qty' => 0]);
    }

    /**
     * Product that is running low.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'qty' => fake()->numberBetween(1, Product::LOW_STOCK_THRESHOLD),
        ]);
    }
}
