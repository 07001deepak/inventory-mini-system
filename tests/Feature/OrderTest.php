<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created_via_api(): void
    {
        Queue::fake();

        $product1 = Product::factory()->create([
            'price_per_unit' => 100.00,
            'tax_percentage' => 18.00,
            'stock_on_hand' => 10,
        ]);

        $product2 = Product::factory()->create([
            'price_per_unit' => 50.00,
            'tax_percentage' => 10.00,
            'stock_on_hand' => 5,
        ]);

        $payload = [
            'customer_name' => 'John Counter',
            'customer_email' => 'john.counter@example.com',
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.customer.email', 'john.counter@example.com');

        // Check database
        $this->assertDatabaseHas('customers', ['email' => 'john.counter@example.com']);
        $this->assertDatabaseHas('orders', ['subtotal' => 250.00, 'tax_total' => 41.00, 'grand_total' => 291.00]);

        // Check stock deduction
        $this->assertEquals(8, $product1->fresh()->stock_on_hand);
        $this->assertEquals(4, $product2->fresh()->stock_on_hand);

        // Assert job queued
        Queue::assertPushed(SendOrderConfirmationEmail::class);
    }

    public function test_order_creation_fails_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create([
            'name' => 'Limited Edition Gadget',
            'stock_on_hand' => 2,
        ]);

        $payload = [
            'customer_name' => 'Alice Customer',
            'customer_email' => 'alice@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5], // Requests 5, only 2 in stock
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Stock should remain unchanged
        $this->assertEquals(2, $product->fresh()->stock_on_hand);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_creates_snapshot_details_in_order_items(): void
    {
        $product = Product::factory()->create([
            'name' => 'Snapshot Mouse',
            'code' => 'SNAP-001',
            'price_per_unit' => 20.00,
            'tax_percentage' => 5.00,
            'stock_on_hand' => 10,
        ]);

        $payload = [
            'customer_name' => 'Bob Builder',
            'customer_email' => 'bob@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ];

        $this->postJson('/api/v1/orders', $payload)->assertStatus(201);

        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Snapshot Mouse',
            'product_code' => 'SNAP-001',
            'unit_price' => 20.00,
            'tax_percentage' => 5.00,
            'quantity' => 3,
            'line_subtotal' => 60.00,
            'line_tax' => 3.00,
            'line_total' => 63.00,
        ]);
    }
}
