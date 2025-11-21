<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\OrderItem;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * Test that a product can be created
     */
    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ]);
    }

    /**
     * Test isInStock method returns true for products with stock
     */
    public function test_is_in_stock_returns_true_when_stock_greater_than_zero(): void
    {
        $product = Product::factory()->inStock()->create();

        $this->assertTrue($product->isInStock());
    }

    /**
     * Test isInStock method returns false for products without stock
     */
    public function test_is_in_stock_returns_false_when_stock_is_zero(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->assertFalse($product->isInStock());
    }

    /**
     * Test product has many order items
     */
    public function test_product_has_many_order_items(): void
    {
        $product = Product::factory()->create();
        OrderItem::factory()->count(5)->forProduct($product)->create();

        $this->assertCount(5, $product->orderItems);
        $this->assertInstanceOf(OrderItem::class, $product->orderItems->first());
    }

    /**
     * Test product can be created with specific price
     */
    public function test_product_can_be_created_with_specific_price(): void
    {
        $product = Product::factory()->price(199.99)->create();

        $this->assertEquals(199.99, $product->price);
    }

    /**
     * Test product can be created with specific stock
     */
    public function test_product_can_be_created_with_specific_stock(): void
    {
        $product = Product::factory()->stock(50)->create();

        $this->assertEquals(50, $product->stock);
    }

    /**
     * Test product can be updated
     */
    public function test_product_can_be_updated(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 50.00,
        ]);

        $product->update([
            'name' => 'Updated Name',
            'price' => 75.00,
        ]);

        $this->assertEquals('Updated Name', $product->fresh()->name);
        $this->assertEquals(75.00, $product->fresh()->price);
    }

    /**
     * Test product can be deleted
     */
    public function test_product_can_be_deleted(): void
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    /**
     * Test product has default category
     */
    public function test_product_can_have_category(): void
    {
        $product = Product::factory()->create(['category' => 'Electronics']);

        $this->assertEquals('Electronics', $product->category);
    }

    /**
     * Test product has description
     */
    public function test_product_can_have_description(): void
    {
        $description = 'This is a test product description';
        $product = Product::factory()->create(['description' => $description]);

        $this->assertEquals($description, $product->description);
    }

    /**
     * Test multiple products can be created
     */
    public function test_multiple_products_can_be_created(): void
    {
        $products = Product::factory()->count(10)->create();

        $this->assertCount(10, $products);
        $this->assertEquals(10, Product::count());
    }

    /**
     * Test product timestamps are set
     */
    public function test_product_has_timestamps(): void
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->created_at);
        $this->assertNotNull($product->updated_at);
    }
}
