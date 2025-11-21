<?php

namespace Tests\Integration\Workflows;

use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class CompleteOrderingWorkflowTest extends TestCase
{
    /**
     * Test complete workflow: register -> login -> browse products -> create order
     */
    public function test_complete_user_registration_and_ordering_workflow(): void
    {
        // Step 1: Register a new user
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $registerResponse->assertStatus(201);
        $token = $registerResponse->json('access_token');

        // Step 2: Verify user is authenticated
        $userResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $userResponse->assertStatus(200)
            ->assertJson([
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
            ]);

        // Step 3: Browse products
        $productsResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/products');

        $productsResponse->assertStatus(200)
            ->assertJsonIsArray();

        // Step 4: Create some products to order
        $product1 = Product::factory()->create(['name' => 'Laptop', 'price' => 999.99]);
        $product2 = Product::factory()->create(['name' => 'Mouse', 'price' => 29.99]);

        // Step 5: Create an order
        $orderResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product1->id, 'quantity' => 1],
                    ['product_id' => $product2->id, 'quantity' => 2],
                ],
            ]);

        $orderResponse->assertStatus(201);
        $orderId = $orderResponse->json('id');

        // Step 6: Verify order was created
        $orderViewResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/orders/{$orderId}");

        $orderViewResponse->assertStatus(200)
            ->assertJson([
                'status' => 'pending',
            ]);

        // Step 7: List user's orders
        $ordersResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/orders');

        $ordersResponse->assertStatus(200)
            ->assertJsonIsArray();
    }

    /**
     * Test workflow: multiple users registering and ordering
     */
    public function test_multiple_users_can_register_and_place_orders(): void
    {
        // Create first user
        $response1 = $this->postJson('/api/register', [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);
        $token1 = $response1->json('access_token');

        // Create second user
        $response2 = $this->postJson('/api/register', [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'password456',
        ]);
        $token2 = $response2->json('access_token');

        // Create products
        $product = Product::factory()->create(['price' => 99.99]);

        // User 1 creates order
        $order1Response = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ]);

        // User 2 creates order
        $order2Response = $this->withHeader('Authorization', "Bearer $token2")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ]);

        $order1Response->assertStatus(201);
        $order2Response->assertStatus(201);

        // Verify both users have their orders
        $orders1 = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson('/api/orders');

        $orders2 = $this->withHeader('Authorization', "Bearer $token2")
            ->getJson('/api/orders');

        $this->assertNotNull($orders1->json('0.id'));
        $this->assertNotNull($orders2->json('0.id'));
    }

    /**
     * Test login workflow after registration
     */
    public function test_user_can_register_then_logout_then_login(): void
    {
        // Register
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token1 = $registerResponse->json('access_token');

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200);

        // Login again
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200);
        $token2 = $loginResponse->json('access_token');

        // Verify new token works
        $userResponse = $this->withHeader('Authorization', "Bearer $token2")
            ->getJson('/api/user');

        $userResponse->assertStatus(200);
    }

    /**
     * Test user can view and manage their profile
     */
    public function test_user_can_view_and_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        // View profile
        $viewResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $viewResponse->assertStatus(200)
            ->assertJson([
                'name' => 'Original Name',
                'email' => 'original@example.com',
            ]);

        // Update profile
        $updateResponse = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/users/{$user->id}", [
                'name' => 'Updated Name',
            ]);

        $updateResponse->assertStatus(200);

        // Verify update
        $verifyResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $verifyResponse->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name',
            ]);
    }

    /**
     * Test complete product management workflow
     */
    public function test_product_lifecycle_workflow(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create product
        $createResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'name' => 'New Product',
                'description' => 'Product Description',
                'price' => 49.99,
                'stock' => 100,
                'category' => 'Electronics',
            ]);

        $createResponse->assertStatus(201);
        $productId = $createResponse->json('id');

        // View product
        $viewResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/products/{$productId}");

        $viewResponse->assertStatus(200)
            ->assertJson([
                'name' => 'New Product',
                'price' => 49.99,
            ]);

        // Update product
        $updateResponse = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/products/{$productId}", [
                'price' => 59.99,
                'stock' => 50,
            ]);

        $updateResponse->assertStatus(200);

        // Verify update
        $verifyResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/products/{$productId}");

        $verifyResponse->assertStatus(200)
            ->assertJson([
                'price' => 59.99,
                'stock' => 50,
            ]);

        // Delete product
        $deleteResponse = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/products/{$productId}");

        $deleteResponse->assertStatus(200);

        // Verify deletion
        $notFoundResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/products/{$productId}");

        $notFoundResponse->assertStatus(404);
    }
}
