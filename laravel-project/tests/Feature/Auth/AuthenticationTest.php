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

    /**
     * Test registration requires all fields
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registration validates email format
     */
    public function test_registration_validates_email_format(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test registration validates password length
     */
    public function test_registration_validates_password_minimum_length(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '12345',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test registration validates name length
     */
    public function test_registration_validates_name_max_length(): void
    {
        $longName = str_repeat('a', 256);
        
        $response = $this->postJson('/api/register', [
            'name' => $longName,
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test registration creates user with default role
     */
    public function test_registration_creates_user_with_default_role(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'user',
        ]);
    }

    /**
     * Test login requires email and password
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login fails with non-existent email
     */
    public function test_login_fails_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test login requires valid email format
     */
    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test successful login returns valid token
     */
    public function test_successful_login_returns_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user',
            ]);

        $this->assertNotEmpty($response->json('access_token'));
    }

    /**
     * Test logout requires authentication
     */
    public function test_logout_without_token_returns_unauthorized(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /**
     * Test logout deletes the token
     */
    public function test_logout_deletes_the_access_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        // Verify token is invalidated by trying to use it
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $response->assertStatus(401);
    }

    /**
     * Test user endpoint returns authenticated user data
     */
    public function test_user_endpoint_returns_correct_user(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]);
    }

    /**
     * Test user endpoint returns user structure
     */
    public function test_user_endpoint_returns_correct_structure(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'role',
            ]);
    }

    /**
     * Test multiple registrations with different users
     */
    public function test_multiple_users_can_register(): void
    {
        $response1 = $this->postJson('/api/register', [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);

        $response2 = $this->postJson('/api/register', [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'password456',
        ]);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'user1@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'user2@example.com']);
    }

    /**
     * Test token contains Bearer type
     */
    public function test_login_returns_bearer_token_type(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['token_type' => 'Bearer']);
    }

    /**
     * Test register returns user object
     */
    public function test_register_response_includes_user_object(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
            ]);
    }

    /**
     * Test registered user has ID
     */
    public function test_registered_user_has_valid_id(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertNotEmpty($response->json('user.id'));
        $this->assertTrue(is_numeric($response->json('user.id')));
    }
}
