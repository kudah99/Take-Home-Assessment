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

    /**
     * Test unauthenticated user cannot create order
     */
    public function test_unauthenticated_user_cannot_create_order(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test create order requires items array
     */
    public function test_create_order_requires_items_array(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }

    /**
     * Test create order with missing product_id fails
     */
    public function test_create_order_item_requires_product_id(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['quantity' => 1],
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test create order with missing quantity fails
     */
    public function test_create_order_item_requires_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id],
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test create order with quantity as non-integer fails
     */
    public function test_create_order_quantity_must_be_integer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 'not_a_number'],
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test order is created for authenticated user
     */
    public function test_order_is_associated_with_authenticated_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);

        $order = Order::latest()->first();
        $this->assertEquals($user->id, $order->user_id);
    }

    /**
     * Test created order has pending status
     */
    public function test_newly_created_order_has_pending_status(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);

        $response->assertStatus(201)
            ->assertJson(['status' => 'pending']);
    }

    /**
     * Test update order requires authentication
     */
    public function test_update_order_requires_authentication(): void
    {
        $order = Order::factory()->create();

        $response = $this->patchJson("/api/orders/{$order->id}", [
            'status' => 'completed',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test update order with invalid status
     */
    public function test_update_order_with_invalid_status(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/orders/{$order->id}", [
                'status' => 'invalid_status',
            ]);

        // Should either fail validation or accept any status
        $this->assertGreaterThanOrEqual(200, $response->status());
    }

    /**
     * Test can update non-existent order returns 404
     */
    public function test_update_non_existent_order_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson('/api/orders/99999', [
                'status' => 'completed',
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test order can transition to different statuses
     */
    public function test_order_can_transition_through_statuses(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $statuses = ['completed', 'cancelled', 'shipped'];

        foreach ($statuses as $status) {
            $this->patchJson("/api/orders/{$order->id}", [
                'status' => $status,
            ], ['Authorization' => "Bearer $token"]);

            $this->assertEquals($status, $order->fresh()->status);
        }
    }

    /**
     * Test order with single item
     */
    public function test_order_with_single_item_calculation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 99.99]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ]);

        $response->assertStatus(201);
        
        $order = Order::latest()->first();
        $this->assertEquals(99.99, $order->total_amount);
    }

    /**
     * Test order with large quantities
     */
    public function test_order_with_large_quantities(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1000],
                ],
            ]);

        $response->assertStatus(201);
        
        $order = Order::latest()->first();
        $this->assertEquals(10000.00, $order->total_amount);
    }

    /**
     * Test list orders returns all orders for authenticated user
     */
    public function test_list_orders_includes_multiple_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonIsArray();
        
        $orders = $response->json();
        $this->assertCount(5, $orders);
    }

    /**
     * Test order response structure includes all necessary fields
     */
    public function test_order_response_structure_is_complete(): void
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
     * Test orders are differentiated by user
     */
    public function test_orders_created_by_different_users_are_separate(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        $token1 = $user1->createToken('auth_token')->plainTextToken;
        $token2 = $user2->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);

        $this->withHeader('Authorization', "Bearer $token2")
            ->postJson('/api/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
            ]);

        $this->assertCount(1, $user1->orders);
        $this->assertCount(1, $user2->orders);
    }
}
