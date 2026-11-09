<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'admin.name' => 'Configured Administrator',
            'admin.email' => 'configured-admin@example.com',
            'admin.password' => 'a-strong-test-password',
        ]);
    }

    public function test_admin_seeder_creates_a_hashed_admin_account(): void
    {
        $this->seed(AdminSeeder::class);

        $admin = User::query()->where('email', 'configured-admin@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertSame('Configured Administrator', $admin->name);
        $this->assertTrue(Hash::check('a-strong-test-password', $admin->password));
    }

    public function test_running_admin_seeder_again_promotes_existing_user_without_resetting_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Existing Account',
            'email' => 'configured-admin@example.com',
            'password' => 'existing-account-password',
            'role' => User::ROLE_USER,
        ]);
        $originalPasswordHash = $user->password;

        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $user->refresh();

        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertSame($originalPasswordHash, $user->password);
        $this->assertTrue(Hash::check('existing-account-password', $user->password));
        $this->assertDatabaseCount('users', 1);
    }
}
