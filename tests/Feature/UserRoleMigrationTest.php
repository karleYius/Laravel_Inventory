<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserRoleMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_migration_preserves_existing_users_and_assigns_user_role(): void
    {
        $user = User::factory()->create();
        $originalId = $user->id;
        $originalEmail = $user->email;
        $originalPassword = $user->password;

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });

        $migration = require database_path('migrations/2026_10_07_044000_add_role_to_users_table.php');
        $migration->up();

        $user->refresh();

        $this->assertSame($originalId, $user->id);
        $this->assertSame($originalEmail, $user->email);
        $this->assertSame($originalPassword, $user->password);
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertDatabaseCount('users', 1);
    }
}
