<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_customers_via_api(): void
    {
        Customer::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_customer_via_api(): void
    {
        $payload = [
            'name' => 'Sarah Connor',
            'email' => 'sarah@skynet.com',
            'phone' => '+1 555-9090',
        ];

        $response = $this->postJson('/api/v1/customers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Sarah Connor')
            ->assertJsonPath('data.email', 'sarah@skynet.com');

        $this->assertDatabaseHas('customers', ['email' => 'sarah@skynet.com']);
    }

    public function test_can_update_customer_via_api(): void
    {
        $customer = Customer::factory()->create(['name' => 'Original Name']);

        $response = $this->putJson("/api/v1/customers/{$customer->id}", [
            'name' => 'Renamed Customer',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Renamed Customer');

        $this->assertEquals('Renamed Customer', $customer->fresh()->name);
    }

    public function test_can_delete_customer_via_api(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }
}
