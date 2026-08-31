<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAndOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_active_products(): void
    {
        Product::factory()->count(3)->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonPath('success', true);
    }

    public function test_guest_cannot_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Producto de prueba',
            'price' => 10,
            'stock' => 5,
            'sku' => 'TEST-001',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_order_and_stock_decreases(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 20]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 60]);
    }

    public function test_order_fails_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(422);
    }
}
