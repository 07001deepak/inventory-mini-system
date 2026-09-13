<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products_via_api(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_product_via_api(): void
    {
        $payload = [
            'name' => 'Test Keyboard',
            'code' => 'TEST-KEY-01',
            'price_per_unit' => 149.99,
            'tax_percentage' => 18.00,
            'stock_on_hand' => 12,
            'low_stock_threshold' => 4,
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Keyboard')
            ->assertJsonPath('data.code', 'TEST-KEY-01');

        $this->assertDatabaseHas('products', ['code' => 'TEST-KEY-01']);
    }

    public function test_can_update_product_via_api(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name', 'stock_on_hand' => 10]);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Name',
            'stock_on_hand' => 25,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.stock_on_hand', 25);

        $this->assertEquals('Updated Name', $product->fresh()->name);
    }

    public function test_can_delete_product_via_api(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
