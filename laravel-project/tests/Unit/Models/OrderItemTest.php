<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    /**
     * Test that an order item can be created
     */
    public function test_order_item_can_be_created(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'price' => $product->price,
        ]);

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    /**
     * Test order item belongs to order
     */
    public function test_order_item_belongs_to_order(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->forOrder($order)->create();

        $this->assertInstanceOf(Order::class, $item->order);
        $this->assertEquals($order->id, $item->order->id);
    }

    /**
     * Test order item belongs to product
     */
    public function test_order_item_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $item = OrderItem::factory()->forProduct($product)->create();

        $this->assertInstanceOf(Product::class, $item->product);
        $this->assertEquals($product->id, $item->product->id);
    }

    /**
     * Test order item can be created with specific quantity
     */
    public function test_order_item_can_be_created_with_quantity(): void
    {
        $item = OrderItem::factory()->create(['quantity' => 25]);

        $this->assertEquals(25, $item->quantity);
    }

    /**
     * Test order item preserves price at time of purchase
     */
    public function test_order_item_preserves_price_at_purchase(): void
    {
        $product = Product::factory()->create(['price' => 99.99]);
        $item = OrderItem::factory()->create([
            'product_id' => $product->id,
            'price' => 99.99,
        ]);

        // Update product price
        $product->update(['price' => 149.99]);

        // Order item price should remain unchanged
        $this->assertEquals(99.99, $item->refresh()->price);
        $this->assertEquals(149.99, $product->refresh()->price);
    }

    /**
     * Test order item can be updated
     */
    public function test_order_item_can_be_updated(): void
    {
        $item = OrderItem::factory()->create(['quantity' => 5]);

        $item->update(['quantity' => 10]);

        $this->assertEquals(10, $item->fresh()->quantity);
    }

    /**
     * Test order item can be deleted
     */
    public function test_order_item_can_be_deleted(): void
    {
        $item = OrderItem::factory()->create();
        $itemId = $item->id;

        $item->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
    }

    /**
     * Test order item has timestamps
     */
    public function test_order_item_has_timestamps(): void
    {
        $item = OrderItem::factory()->create();

        $this->assertNotNull($item->created_at);
        $this->assertNotNull($item->updated_at);
    }

    /**
     * Test multiple order items for same order
     */
    public function test_order_can_have_multiple_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(5)->forOrder($order)->create();

        $this->assertCount(5, $order->items);
    }

    /**
     * Test order item deletion cascades with order
     */
    public function test_order_item_cascades_when_order_deleted(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->forOrder($order)->create();
        $itemId = $item->id;

        $order->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
    }

    /**
     * Test order item cascades when product deleted
     */
    public function test_order_item_cascades_when_product_deleted(): void
    {
        $product = Product::factory()->create();
        $item = OrderItem::factory()->forProduct($product)->create();
        $itemId = $item->id;

        $product->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
    }
}
