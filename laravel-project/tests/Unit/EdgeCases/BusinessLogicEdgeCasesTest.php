<?php

namespace Tests\Unit\EdgeCases;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class BusinessLogicEdgeCasesTest extends TestCase
{
    /**
     * Test order total with large quantities
     */
    public function test_order_total_calculation_with_large_quantities(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['price' => 999.99]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1000,
            'price' => $product->price,
        ]);

        $expected = 1000 * 999.99;
        $this->assertEquals($expected, $order->calculateTotal());
    }

    /**
     * Test order total with decimal prices
     */
    public function test_order_total_with_decimal_prices(): void
    {
        $order = Order::factory()->create();
        
        $product1 = Product::factory()->create(['price' => 19.99]);
        $product2 = Product::factory()->create(['price' => 29.99]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 3,
            'price' => 19.99,
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 2,
            'price' => 29.99,
        ]);

        $expected = (3 * 19.99) + (2 * 29.99);
        $this->assertEquals($expected, $order->calculateTotal());
    }

    /**
     * Test product stock can be zero
     */
    public function test_product_with_zero_stock(): void
    {
        $product = Product::factory()->stock(0)->create();

        $this->assertFalse($product->isInStock());
        $this->assertEquals(0, $product->stock);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 0,
        ]);
    }

    /**
     * Test product with large stock numbers
     */
    public function test_product_with_large_stock(): void
    {
        $product = Product::factory()->stock(999999)->create();

        $this->assertTrue($product->isInStock());
        $this->assertEquals(999999, $product->stock);
    }

    /**
     * Test user with many orders
     */
    public function test_user_with_many_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(100)->create(['user_id' => $user->id]);

        $this->assertCount(100, $user->orders);
    }

    /**
     * Test order with many items
     */
    public function test_order_with_many_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(50)->forOrder($order)->create();

        $this->assertCount(50, $order->items);
    }

    /**
     * Test multiple orders with same product
     */
    public function test_same_product_in_multiple_orders(): void
    {
        $product = Product::factory()->create();
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertCount(2, $product->orderItems);
    }

    /**
     * Test order item price differs from product current price
     */
    public function test_order_item_price_can_differ_from_current_product_price(): void
    {
        $product = Product::factory()->create(['price' => 50.00]);
        
        $item = OrderItem::factory()->create([
            'product_id' => $product->id,
            'price' => 45.00, // Different from current product price
        ]);

        // Update product price
        $product->update(['price' => 60.00]);

        // Order item should maintain original price
        $this->assertEquals(45.00, $item->fresh()->price);
        $this->assertEquals(60.00, $product->fresh()->price);
    }

    /**
     * Test user with no orders
     */
    public function test_user_with_no_orders(): void
    {
        $user = User::factory()->create();

        $this->assertCount(0, $user->orders);
    }

    /**
     * Test empty order (no items)
     */
    public function test_order_with_no_items(): void
    {
        $order = Order::factory()->create();

        $this->assertCount(0, $order->items);
        $this->assertEquals(0, $order->calculateTotal());
    }

    /**
     * Test product with long name
     */
    public function test_product_with_long_name(): void
    {
        $longName = str_repeat('A', 255);
        $product = Product::factory()->create(['name' => $longName]);

        $this->assertEquals($longName, $product->name);
    }

    /**
     * Test product with long description
     */
    public function test_product_with_long_description(): void
    {
        $longDescription = str_repeat('Lorem ipsum dolor sit amet. ', 100);
        $product = Product::factory()->create(['description' => $longDescription]);

        $this->assertEquals($longDescription, $product->description);
    }

    /**
     * Test user deletion with cascading orders
     */
    public function test_user_deletion_cascades_all_orders_and_items(): void
    {
        $user = User::factory()->create();
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id]);
        
        OrderItem::factory()->count(3)->forOrder($order1)->create();
        OrderItem::factory()->count(2)->forOrder($order2)->create();

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order1->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order2->id]);
    }

    /**
     * Test product with minimum price
     */
    public function test_product_with_minimum_price(): void
    {
        $product = Product::factory()->price(0.01)->create();

        $this->assertEquals(0.01, $product->price);
    }

    /**
     * Test product with very large price
     */
    public function test_product_with_very_large_price(): void
    {
        $product = Product::factory()->price(99999.99)->create();

        $this->assertEquals(99999.99, $product->price);
    }

    /**
     * Test order status can be set to any string value
     */
    public function test_order_status_flexibility(): void
    {
        $statuses = ['pending', 'processing', 'completed', 'cancelled', 'refunded'];

        foreach ($statuses as $status) {
            $order = Order::factory()->create(['status' => $status]);
            $this->assertEquals($status, $order->status);
        }
    }

    /**
     * Test multiple product categories
     */
    public function test_products_with_different_categories(): void
    {
        $categories = ['Electronics', 'Clothing', 'Books', 'Food', 'Sports'];
        
        foreach ($categories as $category) {
            Product::factory()->create(['category' => $category]);
        }

        $this->assertCount(5, Product::distinct('category')->pluck('category'));
    }

    /**
     * Test order item quantity is preserved
     */
    public function test_order_item_quantity_precision(): void
    {
        $item = OrderItem::factory()->create(['quantity' => 999]);

        $this->assertEquals(999, $item->quantity);
        
        $item->update(['quantity' => 1]);
        $this->assertEquals(1, $item->fresh()->quantity);
    }
}
