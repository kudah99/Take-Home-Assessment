<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /**
     * Test that an order can be created
     */
    public function test_order_can_be_created(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test order has default status of pending
     */
    public function test_order_has_default_status_of_pending(): void
    {
        $order = Order::factory()->create();

        $this->assertEquals('pending', $order->status);
    }

    /**
     * Test order can have different statuses
     */
    public function test_order_can_have_different_statuses(): void
    {
        $pendingOrder = Order::factory()->pending()->create();
        $completedOrder = Order::factory()->completed()->create();
        $cancelledOrder = Order::factory()->cancelled()->create();

        $this->assertEquals('pending', $pendingOrder->status);
        $this->assertEquals('completed', $completedOrder->status);
        $this->assertEquals('cancelled', $cancelledOrder->status);
    }

    /**
     * Test order belongs to a user
     */
    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /**
     * Test order has many items
     */
    public function test_order_has_many_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->forOrder($order)->create();

        $this->assertCount(3, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }

    /**
     * Test calculate total method
     */
    public function test_calculate_total_returns_correct_sum(): void
    {
        $order = Order::factory()->create();
        
        $product1 = Product::factory()->create(['price' => 50.00]);
        $product2 = Product::factory()->create(['price' => 30.00]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => $product1->price,
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 3,
            'price' => $product2->price,
        ]);

        $expected = (2 * 50.00) + (3 * 30.00);
        $this->assertEquals($expected, $order->calculateTotal());
    }

    /**
     * Test calculate total with single item
     */
    public function test_calculate_total_with_single_item(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['price' => 99.99]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'price' => $product->price,
        ]);

        $this->assertEquals(499.95, $order->calculateTotal());
    }

    /**
     * Test calculate total with empty items
     */
    public function test_calculate_total_with_no_items(): void
    {
        $order = Order::factory()->create();

        $this->assertEquals(0, $order->calculateTotal());
    }

    /**
     * Test order can be updated
     */
    public function test_order_status_can_be_updated(): void
    {
        $order = Order::factory()->pending()->create();

        $order->update(['status' => 'completed']);

        $this->assertEquals('completed', $order->fresh()->status);
    }

    /**
     * Test order can be deleted
     */
    public function test_order_can_be_deleted(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        $order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $orderId]);
    }

    /**
     * Test order deletion cascades to items
     */
    public function test_order_deletion_cascades_to_items(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->forOrder($order)->create();

        $order->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    }

    /**
     * Test order has timestamps
     */
    public function test_order_has_timestamps(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
    }

    /**
     * Test multiple orders for same user
     */
    public function test_user_can_have_multiple_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertCount(5, $user->orders);
    }
}
