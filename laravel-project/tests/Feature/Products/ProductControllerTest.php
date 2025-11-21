<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    /**
     * Test unauthenticated user cannot list products
     */
    public function test_unauthenticated_user_cannot_list_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can list products
     */
    public function test_authenticated_user_can_list_products(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(5)->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonIsArray()
            ->assertJsonCount(5);
    }

    /**
     * Test can retrieve a specific product
     */
    public function test_authenticated_user_can_view_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => 'Test Product',
                'price' => 99.99,
            ]);
    }

    /**
     * Test viewing non-existent product returns 404
     */
    public function test_view_non_existent_product_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/products/99999');

        $response->assertStatus(404);
    }

    /**
     * Test authenticated user can create product
     */
    public function test_authenticated_user_can_create_product(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'name' => 'New Product',
                'description' => 'A new test product',
                'price' => 49.99,
                'stock' => 100,
                'category' => 'Electronics',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New Product',
                'price' => 49.99,
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 49.99,
        ]);
    }

    /**
     * Test create product fails with missing name
     */
    public function test_create_product_fails_with_missing_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'description' => 'A new test product',
                'price' => 49.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test authenticated user can update product
     */
    public function test_authenticated_user_can_update_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Old Name',
            'price' => 50.00,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/products/{$product->id}", [
                'name' => 'Updated Name',
                'price' => 75.00,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 75.00,
        ]);
    }

    /**
     * Test authenticated user can delete product
     */
    public function test_authenticated_user_can_delete_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test product response includes all fields
     */
    public function test_product_response_includes_all_fields(): void
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
     * Test product with zero stock is still available
     */
    public function test_out_of_stock_product_can_be_retrieved(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->outOfStock()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['stock' => 0]);
    }
}
