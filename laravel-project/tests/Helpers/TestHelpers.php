<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

/**
 * Test Helper class with utility methods for common test operations
 */
class TestHelpers
{
    /**
     * Create an authenticated user token
     */
    public static function createAuthenticatedUser(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
            'header' => ['Authorization' => "Bearer $token"],
        ];
    }

    /**
     * Create an authenticated admin user token
     */
    public static function createAuthenticatedAdmin(array $attributes = []): array
    {
        $admin = User::factory()->admin()->create($attributes);
        $token = $admin->createToken('auth_token')->plainTextToken;
        
        return [
            'user' => $admin,
            'token' => $token,
            'header' => ['Authorization' => "Bearer $token"],
        ];
    }

    /**
     * Create a test user with orders
     */
    public static function createUserWithOrders(int $orderCount = 3, int $itemsPerOrder = 2): User
    {
        $user = User::factory()->create();
        
        for ($i = 0; $i < $orderCount; $i++) {
            $order = Order::factory()->create(['user_id' => $user->id]);
            
            for ($j = 0; $j < $itemsPerOrder; $j++) {
                $product = Product::factory()->create();
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ]);
            }
        }
        
        return $user->load('orders.items');
    }

    /**
     * Create sample products for testing
     */
    public static function createSampleProducts(int $count = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Product::factory()->count($count)->create();
    }

    /**
     * Create an order with specified items
     */
    public static function createOrderWithItems(User $user, array $items): Order
    {
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        foreach ($items as $item) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'] ?? 1,
            ]);
        }
        
        return $order->load('items');
    }

    /**
     * Assert that a response has JSON validation errors
     */
    public static function assertJsonValidationError(
        \Illuminate\Testing\TestResponse $response,
        string $field
    ): void {
        $response->assertStatus(422);
        $errors = $response->json('errors');
        
        if (!isset($errors[$field])) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected validation error for field '{$field}'"
            );
        }
    }

    /**
     * Assert that a response is authenticated
     */
    public static function assertAuthenticated(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'name', 'email']);
    }

    /**
     * Assert that a response is unauthenticated
     */
    public static function assertUnauthenticated(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(401);
    }

    /**
     * Create a complete order workflow
     */
    public static function createCompleteOrderWorkflow(
        int $productCount = 3,
        int $ordersPerUser = 2
    ): array {
        $products = self::createSampleProducts($productCount);
        $users = User::factory()->count(2)->create();
        $orders = [];

        foreach ($users as $user) {
            for ($i = 0; $i < $ordersPerUser; $i++) {
                $order = Order::factory()->create(['user_id' => $user->id]);
                
                foreach ($products->random(2) as $product) {
                    OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                    ]);
                }
                
                $orders[] = $order;
            }
        }

        return [
            'products' => $products,
            'users' => $users,
            'orders' => collect($orders),
        ];
    }
}
