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
     * Test user cascades delete to orders
     */
    public function test_user_deletion_cascades_to_orders(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
