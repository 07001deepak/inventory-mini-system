<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'customer_id' => Customer::factory(),
            'subtotal' => 100.00,
            'tax_total' => 18.00,
            'grand_total' => 118.00,
            'status' => 'completed',
        ];
    }
}
