<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Order;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Test that a user can be created
     */
    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test that a user has the correct default role
     */
    public function test_user_has_default_role_of_user(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('user', $user->role);
    }

    /**
     * Test that a user can be created as an admin
     */
    public function test_user_can_be_created_as_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals('admin', $admin->role);
    }

    /**
     * Test isAdmin method returns true for admin users
     */
    public function test_is_admin_returns_true_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Test isAdmin method returns false for regular users
     */
    public function test_is_admin_returns_false_for_regular_user(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test a user has many orders
     */
    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    /**
     * Test user password is hidden
     */
    public function test_user_password_is_hidden_in_array(): void
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
    }

    /**
     * Test user email is unique
     */
    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'duplicate@example.com']);
    }

    /**
     * Test user can be updated
     */
    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $user->update(['name' => 'New Name']);

        $this->assertEquals('New Name', $user->fresh()->name);
    }

    /**
     * Test user can be deleted
     */
    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /**
     * Test user deletion cascades to orders
     */
    public function test_user_deletion_cascades_to_orders(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /**
     * Test user password is hidden from array
     */
    public function test_user_password_is_hidden_from_json_array(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    /**
     * Test user remember token is hidden
     */
    public function test_user_remember_token_is_hidden_from_array(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /**
     * Test user email is visible in array
     */
    public function test_user_email_is_visible_in_array(): void
    {
        $user = User::factory()->create(['email' => 'visible@example.com']);
        $array = $user->toArray();

        $this->assertArrayHasKey('email', $array);
        $this->assertEquals('visible@example.com', $array['email']);
    }

    /**
     * Test user name is visible in array
     */
    public function test_user_name_is_visible_in_array(): void
    {
        $user = User::factory()->create(['name' => 'Test Name']);
        $array = $user->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertEquals('Test Name', $array['name']);
    }

    /**
     * Test user role is visible in array
     */
    public function test_user_role_is_visible_in_array(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $array = $user->toArray();

        $this->assertArrayHasKey('role', $array);
        $this->assertEquals('admin', $array['role']);
    }

    /**
     * Test isAdmin method for admin
     */
    public function test_is_admin_method_returns_true_for_admin(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($adminUser->isAdmin());
    }

    /**
     * Test user with non-admin role isAdmin returns false
     */
    public function test_non_admin_user_is_not_admin(): void
    {
        $user = User::factory()->create(['role' => 'moderator']);

        $this->assertFalse($user->isAdmin());
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
     * Test user can have multiple orders
     */
    public function test_user_can_have_multiple_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
        foreach ($user->orders as $order) {
            $this->assertEquals($user->id, $order->user_id);
        }
    }

    /**
     * Test user timestamps are set
     */
    public function test_user_has_timestamps(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    /**
     * Test user created_at is recent
     */
    public function test_user_created_at_is_recent(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->created_at->diffInMinutes(now()) < 1);
    }

    /**
     * Test user updated_at is recent
     */
    public function test_user_updated_at_is_recent(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->updated_at->diffInMinutes(now()) < 1);
    }

    /**
     * Test user updated_at changes on update
     */
    public function test_user_updated_at_changes_on_update(): void
    {
        $user = User::factory()->create();
        $originalUpdatedAt = $user->updated_at;

        sleep(1);
        $user->update(['name' => 'New Name']);

        $this->assertNotEquals($originalUpdatedAt->timestamp, $user->fresh()->updated_at->timestamp);
    }

    /**
     * Test user email is verified cast
     */
    public function test_user_email_verified_at_cast(): void
    {
        $user = User::factory()->create();

        // Should be null or a datetime
        $this->assertTrue($user->email_verified_at === null || $user->email_verified_at instanceof \DateTime);
    }

    /**
     * Test user can have api tokens
     */
    public function test_user_can_create_api_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token');

        $this->assertNotNull($token);
        $this->assertNotEmpty($token->plainTextToken);
    }

    /**
     * Test user api tokens can be used
     */
    public function test_user_api_tokens_are_created(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token');

        // Verify token was created
        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test_token',
        ]);
    }

    /**
     * Test user attributes can be accessed
     */
    public function test_user_attributes_can_be_accessed(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'user',
        ]);

        $this->assertEquals('John Doe', $user->getAttribute('name'));
        $this->assertEquals('john@example.com', $user->getAttribute('email'));
        $this->assertEquals('user', $user->getAttribute('role'));
    }

    /**
     * Test user magic getter methods
     */
    public function test_user_magic_getters(): void
    {
        $user = User::factory()->create([
            'name' => 'Magic User',
            'email' => 'magic@example.com',
        ]);

        $this->assertEquals('Magic User', $user->name);
        $this->assertEquals('magic@example.com', $user->email);
    }

    /**
     * Test creating user with different roles
     */
    public function test_user_can_have_different_roles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('user', $user->role);
    }

    /**
     * Test user password is hashed
     */
    public function test_user_password_is_hashed(): void
    {
        $plainPassword = 'plainpassword123';
        $user = User::factory()->create(['password' => $plainPassword]);

        $this->assertNotEquals($plainPassword, $user->password);
    }

    /**
     * Test user can be found by email
     */
    public function test_user_can_be_found_by_email(): void
    {
        $email = 'findme@example.com';
        $user = User::factory()->create(['email' => $email]);

        $found = User::where('email', $email)->first();

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    /**
     * Test user can be found by id
     */
    public function test_user_can_be_found_by_id(): void
    {
        $user = User::factory()->create();

        $found = User::find($user->id);

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    /**
     * Test user notifications trait is available
     */
    public function test_user_has_notifiable_trait(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Notifications\Notifiable::class, $user);
    }

    /**
     * Test user has api tokens trait
     */
    public function test_user_has_api_tokens_trait(): void
    {
        $user = User::factory()->create();
        
        // User should be able to create tokens
        $token = $user->createToken('test');
        $this->assertNotNull($token);
    }

    /**
     * Test user fillable includes required fields
     */
    public function test_user_fillable_includes_all_required_fields(): void
    {
        $user = new User([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'user',
        ]);

        $this->assertEquals('Test', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('user', $user->role);
    }

    /**
     * Test user factory creates valid user
     */
    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->role);
    }

    /**
     * Test user factory admin method creates admin user
     */
    public function test_user_factory_admin_creates_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals('admin', $admin->role);
        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Test user collection methods
     */
    public function test_user_collection_operations(): void
    {
        User::factory()->count(3)->create();

        $users = User::all();

        $this->assertGreaterThanOrEqual(3, $users->count());
    }

    /**
     * Test user exists in database
     */
    public function test_user_exists_in_database(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(User::where('email', $user->email)->exists());
    }

    /**
     * Test user does not exist in database after deletion
     */
    public function test_user_does_not_exist_after_deletion(): void
    {
        $user = User::factory()->create();
        $email = $user->email;

        $user->delete();

        $this->assertFalse(User::where('email', $email)->exists());
    }
}
