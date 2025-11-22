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

    /**
     * Test unauthenticated user cannot create product
     */
    public function test_unauthenticated_user_cannot_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'price' => 49.99,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test create product requires name field
     */
    public function test_create_product_requires_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'description' => 'A test product',
                'price' => 49.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test create product with valid data
     */
    public function test_create_product_with_all_fields(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'name' => 'Complete Product',
                'description' => 'Full description',
                'price' => 99.99,
                'stock' => 50,
                'category' => 'Electronics',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Complete Product',
                'description' => 'Full description',
                'price' => 99.99,
                'stock' => 50,
                'category' => 'Electronics',
            ]);
    }

    /**
     * Test product can be created with minimal data
     */
    public function test_create_product_with_minimal_data(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'name' => 'Minimal Product',
            ]);

        $response->assertStatus(201)
            ->assertJson(['name' => 'Minimal Product']);
    }

    /**
     * Test unauthenticated user cannot update product
     */
    public function test_unauthenticated_user_cannot_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->patchJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test update product with partial data
     */
    public function test_update_product_with_partial_data(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 50.00,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/products/{$product->id}", [
                'description' => 'New description',
            ]);

        $response->assertStatus(200);
        
        $updated = $product->fresh();
        $this->assertEquals('Original Name', $updated->name);
        $this->assertEquals('New description', $updated->description);
    }

    /**
     * Test update product price
     */
    public function test_update_product_price(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/products/{$product->id}", [
                'price' => 75.50,
            ]);

        $this->assertEquals(75.50, $product->fresh()->price);
    }

    /**
     * Test update product stock
     */
    public function test_update_product_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/products/{$product->id}", [
                'stock' => 50,
            ]);

        $this->assertEquals(50, $product->fresh()->stock);
    }

    /**
     * Test update non-existent product returns 404
     */
    public function test_update_non_existent_product_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson('/api/products/99999', [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test unauthenticated user cannot delete product
     */
    public function test_unauthenticated_user_cannot_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(401);
    }

    /**
     * Test delete non-existent product returns 404
     */
    public function test_delete_non_existent_product_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/products/99999');

        $response->assertStatus(404);
    }

    /**
     * Test delete product removes it from database
     */
    public function test_delete_product_removes_from_database(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Product to Delete',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/products/{$product->id}");

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test products list structure
     */
    public function test_products_list_has_correct_structure(): void
    {
        $user = User::factory()->create();
        Product::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonIsArray();

        $products = $response->json();
        if (count($products) > 0) {
            $this->assertArrayHasKey('id', $products[0]);
            $this->assertArrayHasKey('name', $products[0]);
        }
    }

    /**
     * Test multiple products can be listed
     */
    public function test_multiple_products_are_listed(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(10)->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(10);
    }

    /**
     * Test product can be found by id
     */
    public function test_specific_product_retrieval_by_id(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/products/{$product1->id}");

        $response->assertStatus(200)
            ->assertJson(['name' => 'Product 1', 'id' => $product1->id]);
    }

    /**
     * Test product creation stores in database
     */
    public function test_created_product_is_stored_in_database(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'name' => 'Database Test Product',
                'price' => 123.45,
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Database Test Product',
            'price' => 123.45,
        ]);
    }

    /**
     * Test product creation returns created product
     */
    public function test_create_product_returns_the_created_product(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'name' => 'New Product',
                'price' => 50.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'price',
            ]);
    }

    /**
     * Test product update returns updated product
     */
    public function test_update_product_returns_updated_data(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Old Name']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/products/{$product->id}", [
                'name' => 'New Name',
            ]);

        $response->assertStatus(200)
            ->assertJson(['name' => 'New Name']);
    }
}
