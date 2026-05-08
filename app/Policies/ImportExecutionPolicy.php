<?php

namespace App\Policies;

use App\Models\User;

class ImportExecutionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function view(User $user): bool
    {
        return $user->active;
    }

    public function create(User $user): bool
    {
        return $user->active && $user->canWrite();
    }
}
