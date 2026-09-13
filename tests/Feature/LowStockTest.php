<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_endpoint_returns_products_at_or_below_threshold(): void
    {
        // High stock product (15 stock, threshold 5)
        Product::factory()->create([
            'name' => 'High Stock Item',
            'stock_on_hand' => 15,
            'low_stock_threshold' => 5,
        ]);

        // Low stock product (3 stock, threshold 5)
        $lowStockProduct = Product::factory()->create([
            'name' => 'Low Stock Item',
            'stock_on_hand' => 3,
            'low_stock_threshold' => 5,
        ]);

        $response = $this->getJson('/api/v1/products/low-stock');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $lowStockProduct->id);
    }

    public function test_low_stock_endpoint_accepts_custom_threshold_param(): void
    {
        Product::factory()->create(['stock_on_hand' => 8]);
        $prod2 = Product::factory()->create(['stock_on_hand' => 4]);

        $response = $this->getJson('/api/v1/products/low-stock?threshold=5');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $prod2->id);
    }
}
