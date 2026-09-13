<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'code' => 'SKU-' . strtoupper($this->faker->bothify('??###')),
            'price_per_unit' => $this->faker->randomFloat(2, 10, 500),
            'tax_percentage' => $this->faker->randomElement([0.00, 5.00, 12.00, 18.00]),
            'stock_on_hand' => $this->faker->numberBetween(1, 50),
            'low_stock_threshold' => 5,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_on_hand' => $this->faker->numberBetween(1, 4),
            'low_stock_threshold' => 5,
        ]);
    }
}
