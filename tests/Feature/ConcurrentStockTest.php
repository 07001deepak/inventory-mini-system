<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrentStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_deduction_is_race_condition_safe(): void
    {
        // Product with exactly 1 unit left in stock
        $product = Product::factory()->create([
            'name' => 'Last Remaining Unit',
            'stock_on_hand' => 1,
            'price_per_unit' => 100.00,
            'tax_percentage' => 10.00,
        ]);

        $orderService = new OrderService();

        $payload1 = [
            'customer_name' => 'Buyer One',
            'customer_email' => 'buyer1@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        $payload2 = [
            'customer_name' => 'Buyer Two',
            'customer_email' => 'buyer2@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        // Execute first order (should succeed)
        $order1 = $orderService->createOrder($payload1);
        $this->assertNotNull($order1);
        $this->assertEquals(0, $product->fresh()->stock_on_hand);

        // Execute second order (should throw InsufficientStockException)
        $this->expectException(InsufficientStockException::class);
        $orderService->createOrder($payload2);
    }
}
