<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait HandlesRoles
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $user->active;
    }

    public function create(User $user): bool
    {
        return $user->active && $user->canWrite();
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $user->active && $user->canWrite();
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $user->active && $user->isAdmin();
    }
}
