<?php

namespace Tests\Integration\Serialization;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class DataSerializationTest extends TestCase
{
    /**
     * Test user model serialization
     */
    public function test_user_serializes_correctly(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $data = $user->toArray();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('role', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
    }

    /**
     * Test product model serialization
     */
    public function test_product_serializes_correctly(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $data = $product->toArray();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('stock', $data);
        $this->assertEquals(99.99, $data['price']);
    }

    /**
     * Test order model serialization with relationships
     */
    public function test_order_serializes_correctly(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $data = $order->toArray();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('total_amount', $data);
    }

    /**
     * Test order item serialization
     */
    public function test_order_item_serializes_correctly(): void
    {
        $item = OrderItem::factory()->create([
            'quantity' => 5,
            'price' => 99.99,
        ]);

        $data = $item->toArray();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('order_id', $data);
        $this->assertArrayHasKey('product_id', $data);
        $this->assertArrayHasKey('quantity', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertEquals(5, $data['quantity']);
    }

    /**
     * Test JSON response from API endpoint
     */
    public function test_api_user_endpoint_serializes_correctly(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * Test JSON response from product endpoint
     */
    public function test_api_product_endpoint_serializes_correctly(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'price',
                'stock',
                'category',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * Test JSON response from order endpoint
     */
    public function test_api_order_endpoint_serializes_correctly(): void
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
     * Test password is not serialized in user response
     */
    public function test_user_password_is_not_in_response(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonMissing(['password']);
    }

    /**
     * Test remember token is not serialized
     */
    public function test_user_remember_token_is_not_in_response(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonMissing(['remember_token']);
    }

    /**
     * Test collection of users serializes correctly
     */
    public function test_user_collection_serializes_correctly(): void
    {
        $user = User::factory()->create();
        User::factory()->count(4)->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonIsArray()
            ->assertJsonCount(5);
    }
}
