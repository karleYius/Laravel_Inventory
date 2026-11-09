<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\LastAdministratorGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    public function index()
    {
        return view('users.index', [
            'users' => User::query()
                ->select(['id', 'name', 'email', 'role', 'created_at'])
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function show(User $user)
    {
        $user = User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at', 'updated_at'])
            ->findOrFail($user->id);

        return view('users.show', compact('user'));
    }

    public function updateRole(
        Request $request,
        User $user,
        LastAdministratorGuard $lastAdministratorGuard,
        ActivityLogger $activityLogger
    ) {
        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
        ]);

        DB::transaction(function () use ($request, $user, $validated, $lastAdministratorGuard, $activityLogger): void {
            $administrators = $lastAdministratorGuard->lockAdministrators();
            $target = User::query()->lockForUpdate()->findOrFail($user->id);
            $before = $target->role;
            $newRole = $validated['role'];

            if ($before === $newRole) {
                return;
            }

            if ($before === User::ROLE_ADMIN && $newRole === User::ROLE_USER) {
                $lastAdministratorGuard->ensureAnotherAdministratorRemains($target, $administrators);
            }

            $target->role = $newRole;
            $target->save();
            $activityLogger->log($request->user(), 'user.role_changed', $target, [
                'before' => ['role' => $before],
                'after' => ['role' => $newRole],
            ]);
        });

        return redirect()->route('users.show', $user)->with('success', 'User role updated successfully.');
    }

    public function destroy(
        Request $request,
        User $user,
        LastAdministratorGuard $lastAdministratorGuard,
        ActivityLogger $activityLogger
    ) {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'user' => 'Use your profile page to delete your own account.',
            ]);
        }

        DB::transaction(function () use ($request, $user, $lastAdministratorGuard, $activityLogger): void {
            $administrators = $lastAdministratorGuard->lockAdministrators();
            $target = User::query()->lockForUpdate()->findOrFail($user->id);
            $lastAdministratorGuard->ensureAnotherAdministratorRemains($target, $administrators);

            $activityLogger->log($request->user(), 'user.deleted', $target, [
                'before' => ['name' => $target->name, 'email' => $target->email, 'role' => $target->role],
            ]);
            $target->delete();
        });

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
