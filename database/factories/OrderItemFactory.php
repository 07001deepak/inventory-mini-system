<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(2, true),
            'product_code' => 'SKU-' . strtoupper($this->faker->bothify('??###')),
            'unit_price' => 50.00,
            'tax_percentage' => 18.00,
            'quantity' => 2,
            'line_subtotal' => 100.00,
            'line_tax' => 18.00,
            'line_total' => 118.00,
        ];
    }
}
