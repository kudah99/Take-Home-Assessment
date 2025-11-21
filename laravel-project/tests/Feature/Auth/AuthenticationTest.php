<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    /**
     * Test user can register with valid credentials
     */
    public function test_user_can_register_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Kudakwashe Chipangura',
            'email' => 'kcchipangura@gmail.com',
            'password' => 'vd7uHW5M',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Kudakwashe Chipangura',
            'email' => 'kcchipangura@gmail.com',
        ]);
    }

    /**
     * Test registration fails with missing name
     */
    public function test_registration_fails_with_missing_name(): void
    {
        $response = $this->postJson('/api/register', [
            'email' => 'kcchipangura@gmail.com',
            'password' => 'vd7uHW5M',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test registration fails with invalid email
     */
    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Kudakwashe Chipangura',
            'email' => 'invalid-email',
            'password' => 'vd7uHW5M',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test registration fails with short password
     */
    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Kudakwashe Chipangura',
            'email' => 'kcchipangura@gmail.com',
            'password' => 'pass',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'kcchipangura@gmail.com',
            'password' => bcrypt('vd7uHW5M'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'kcchipangura@gmail.com',
            'password' => 'vd7uHW5M',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email'],
            ]);
    }

    /**
     * Test login fails with invalid email
     */
    public function test_login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'vd7uHW5M',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test login fails with incorrect password
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'kcchipangura@gmail.com',
            'password' => bcrypt('vd7uHW5M'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'kcchipangura@gmail.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test user can logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        // Verify token is deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);
    }

    /**
     * Test logout fails without authentication
     */
    public function test_logout_fails_without_authentication(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can get user data
     */
    public function test_authenticated_user_can_get_user_data(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /**
     * Test unauthenticated user cannot get user data
     */
    public function test_unauthenticated_user_cannot_get_user_data(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
