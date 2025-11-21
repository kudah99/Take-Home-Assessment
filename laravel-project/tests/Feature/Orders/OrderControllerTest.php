<?php

namespace Tests\Feature\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    /**
     * Test unauthenticated user cannot list orders
     */
    public function test_unauthenticated_user_cannot_list_orders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can list orders
     */
    public function test_authenticated_user_can_list_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    /**
     * Test can retrieve a specific order
     */
    public function test_authenticated_user_can_view_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
                'user_id' => $user->id,
                'status' => $order->status,
            ]);
    }

    /**
     * Test viewing non-existent order returns 404
     */
    public function test_view_non_existent_order_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/orders/99999');

        $response->assertStatus(404);
    }

    /**
     * Test authenticated user can create order
     */
    public function test_authenticated_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 50.00]);
        $product2 = Product::factory()->create(['price' => 30.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_id' => $product1->id,
                        'quantity' => 2,
                    ],
                    [
                        'product_id' => $product2->id,
                        'quantity' => 3,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test create order without items fails
     */
    public function test_create_order_without_items_fails(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [],
            ]);

        // Should fail validation or not create
        $this->assertTrue($response->status() >= 400 || Order::count() === 0);
    }

    /**
     * Test create order with non-existent product fails
     */
    public function test_create_order_with_non_existent_product_fails(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_id' => 99999,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(500);
    }

    /**
     * Test authenticated user can update order status
     */
    public function test_authenticated_user_can_update_order_status(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/orders/{$order->id}", [
                'status' => 'completed',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test order has correct structure
     */
    public function test_order_response_has_correct_structure(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'user_id',
                'status',
                'total_amount',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * Test order with multiple items is created correctly
     */
    public function test_order_with_multiple_items_is_created(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 50.00]);
        $product2 = Product::factory()->create(['price' => 75.00]);
        $product3 = Product::factory()->create(['price' => 25.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product1->id, 'quantity' => 1],
                    ['product_id' => $product2->id, 'quantity' => 2],
                    ['product_id' => $product3->id, 'quantity' => 3],
                ],
            ]);

        $response->assertStatus(201);
        
        $order = Order::latest()->first();
        $this->assertCount(3, $order->items);
    }

    /**
     * Test order items are properly associated
     */
    public function test_order_items_are_properly_associated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 99.99]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5,
                    ],
                ],
            ]);

        $response->assertStatus(201);
        
        $order = Order::latest()->first();
        $item = $order->items->first();
        
        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(5, $item->quantity);
    }

    /**
     * Test order total amount calculation
     */
    public function test_order_calculates_total_amount(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 50.00]);
        $product2 = Product::factory()->create(['price' => 30.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 3],
            ],
        ], ['Authorization' => "Bearer $token"]);

        $order = Order::latest()->first();
        $expected = (2 * 50.00) + (3 * 30.00);
        
        $this->assertEquals($expected, $order->total_amount);
    }

    /**
     * Test multiple users can have orders
     */
    public function test_multiple_users_can_have_orders(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Order::factory()->count(3)->create(['user_id' => $user1->id]);
        Order::factory()->count(2)->create(['user_id' => $user2->id]);

        $this->assertCount(3, $user1->orders);
        $this->assertCount(2, $user2->orders);
    }

    /**
     * Test order status transitions are tracked
     */
    public function test_order_status_transitions_are_tracked(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        // Initial status
        $this->assertEquals('pending', $order->status);

        // Update to completed
        $this->patchJson("/api/orders/{$order->id}", [
            'status' => 'completed',
        ], ['Authorization' => "Bearer $token"]);

        $this->assertEquals('completed', $order->fresh()->status);
    }
}
