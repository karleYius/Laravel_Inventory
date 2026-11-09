<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class LastAdministratorGuard
{
    /**
     * Lock all current administrator rows so concurrent removals serialize.
     *
     * @return Collection<int, User>
     */
    public function lockAdministrators(): Collection
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id', 'role']);
    }

    /**
     * @param Collection<int, User> $administrators
     */
    public function ensureAnotherAdministratorRemains(User $target, Collection $administrators): void
    {
        if ($target->isAdmin() && $administrators->count() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'The last administrator cannot be demoted or deleted.',
            ]);
        }
    }
}
