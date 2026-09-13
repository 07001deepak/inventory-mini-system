<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_customer_order_history_by_email(): void
    {
        $customer = Customer::factory()->create(['email' => 'history@example.com']);
        $product = Product::factory()->create(['stock_on_hand' => 20]);

        // Create 2 orders for customer
        Order::factory()->create(['customer_id' => $customer->id, 'order_number' => 'ORD-TEST-001', 'subtotal' => 100, 'tax_total' => 18, 'grand_total' => 118]);
        Order::factory()->create(['customer_id' => $customer->id, 'order_number' => 'ORD-TEST-002', 'subtotal' => 200, 'tax_total' => 36, 'grand_total' => 236]);

        $response = $this->getJson('/api/v1/customers/orders?email=history@example.com');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_customer_order_history_returns_empty_when_email_has_no_orders(): void
    {
        $response = $this->getJson('/api/v1/customers/orders?email=nonexistent@example.com');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
