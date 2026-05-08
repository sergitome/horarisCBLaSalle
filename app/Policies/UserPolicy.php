<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\HandlesRoles;

class UserPolicy
{
    use HandlesRoles;
}
