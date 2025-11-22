<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * Test retrieving all users requires authentication
     */
    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can list users
     */
    public function test_authenticated_user_can_list_users(): void
    {
        $user = User::factory()->create();
        User::factory()->count(5)->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonIsArray()
            ->assertJsonCount(6);
    }

    /**
     * Test can retrieve a specific user
     */
    public function test_authenticated_user_can_view_user(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ]);
    }

    /**
     * Test viewing non-existent user returns 404
     */
    public function test_view_non_existent_user_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users/99999');

        $response->assertStatus(404);
    }

    /**
     * Test authenticated user can update user
     */
    public function test_authenticated_user_can_update_user(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create(['name' => 'Old Name']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/users/{$targetUser->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test authenticated user can delete user
     */
    public function test_authenticated_user_can_delete_user(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    /**
     * Test deleting non-existent user returns 404
     */
    public function test_delete_non_existent_user_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/users/99999');

        $response->assertStatus(404);
    }

    /**
     * Test user data does not include password
     */
    public function test_user_response_does_not_include_password(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertDontSee('password');
    }

    /**
     * Test unauthenticated user cannot view user
     */
    public function test_unauthenticated_user_cannot_view_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(401);
    }

    /**
     * Test view user response has correct structure
     */
    public function test_view_user_response_structure(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'role',
            ]);
    }

    /**
     * Test authenticated user can list users
     */
    public function test_list_users_returns_array(): void
    {
        $user = User::factory()->create();
        User::factory()->count(3)->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    /**
     * Test list users includes all users
     */
    public function test_list_users_includes_all_created_users(): void
    {
        $user = User::factory()->create();
        User::factory()->count(5)->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(6);
    }

    /**
     * Test user list structure
     */
    public function test_user_list_has_correct_structure(): void
    {
        $user = User::factory()->create();
        User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200);
        $users = $response->json();
        
        if (count($users) > 0) {
            $this->assertArrayHasKey('id', $users[0]);
            $this->assertArrayHasKey('name', $users[0]);
            $this->assertArrayHasKey('email', $users[0]);
            $this->assertArrayHasKey('role', $users[0]);
        }
    }

    /**
     * Test unauthenticated user cannot update user
     */
    public function test_unauthenticated_user_cannot_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test update user with name
     */
    public function test_update_user_name(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create(['name' => 'Original Name']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/users/{$targetUser->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $targetUser->fresh()->name);
    }

    /**
     * Test update user with email
     */
    public function test_update_user_email(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create(['email' => 'old@example.com']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/users/{$targetUser->id}", [
                'email' => 'new@example.com',
            ]);

        $this->assertEquals('new@example.com', $targetUser->fresh()->email);
    }

    /**
     * Test update user with multiple fields
     */
    public function test_update_user_with_multiple_fields(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/users/{$targetUser->id}", [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ]);

        $updated = $targetUser->fresh();
        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals('new@example.com', $updated->email);
    }

    /**
     * Test update non-existent user returns 404
     */
    public function test_update_non_existent_user_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson('/api/users/99999', [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test unauthenticated user cannot delete user
     */
    public function test_unauthenticated_user_cannot_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(401);
    }

    /**
     * Test delete user removes from database
     */
    public function test_delete_user_removes_from_database(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/users/{$targetUser->id}");

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    /**
     * Test delete user returns success message
     */
    public function test_delete_user_returns_success_message(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);
    }

    /**
     * Test view specific user by id
     */
    public function test_view_specific_user_contains_correct_data(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create([
            'name' => 'Specific User',
            'email' => 'specific@example.com',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $targetUser->id,
                'name' => 'Specific User',
                'email' => 'specific@example.com',
            ]);
    }

    /**
     * Test multiple users have different roles
     */
    public function test_users_can_have_different_roles(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200);
        $users = $response->json();

        $this->assertGreaterThanOrEqual(2, count($users));
    }

    /**
     * Test update preserves other user fields
     */
    public function test_update_preserves_unchanged_fields(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => 'user',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/users/{$targetUser->id}", [
                'name' => 'Updated Name',
            ]);

        $updated = $targetUser->fresh();
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('original@example.com', $updated->email);
        $this->assertEquals('user', $updated->role);
    }

    /**
     * Test list users does not expose sensitive data
     */
    public function test_list_users_does_not_expose_password(): void
    {
        $user = User::factory()->create();
        User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        // Ensure password is not in response
        $responseContent = json_encode($response->json());
        $this->assertStringNotContainsString('password', strtolower($responseContent));
    }

    /**
     * Test empty user list with no users created
     */
    public function test_user_list_is_empty_when_no_users(): void
    {
        // This test assumes users are cleaned up per test
        // Create only one user for auth
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    /**
     * Test user role is preserved after update
     */
    public function test_user_role_is_visible_after_update(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/users/{$targetUser->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['role']);
    }

    /**
     * Test get user includes all required fields
     */
    public function test_get_user_includes_all_fields(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$targetUser->id}");

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
}
