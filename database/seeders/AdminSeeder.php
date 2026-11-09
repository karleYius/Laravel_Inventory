<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Create or promote the configured administrator account.
     */
    public function run(): void
    {
        $name = config('admin.name');
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($name) || trim($name) === ''
            || ! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || ! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException(
                'Set ADMIN_NAME, a valid ADMIN_EMAIL, and an ADMIN_PASSWORD of at least 12 characters before running AdminSeeder.'
            );
        }

        DB::transaction(function () use ($name, $email, $password): void {
            $activityLogger = app(ActivityLogger::class);
            $admin = User::query()->where('email', $email)->lockForUpdate()->first();

            if ($admin) {
                if ($admin->role !== User::ROLE_ADMIN) {
                    $previousRole = $admin->role;
                    $admin->role = User::ROLE_ADMIN;
                    $admin->save();
                    $activityLogger->log(null, 'user.role_changed', $admin, [
                        'before' => ['role' => $previousRole],
                        'after' => ['role' => User::ROLE_ADMIN],
                    ]);
                }

                return;
            }

            $admin = new User;
            $admin->name = $name;
            $admin->email = $email;
            $admin->password = Hash::make($password);
            $admin->role = User::ROLE_ADMIN;
            $admin->save();

            $activityLogger->log(null, 'user.provisioned', $admin, [
                'after' => ['name' => $name, 'email' => $email, 'role' => User::ROLE_ADMIN],
            ]);
        });
    }
}
