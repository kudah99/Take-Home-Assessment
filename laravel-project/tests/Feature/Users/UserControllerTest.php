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
}
