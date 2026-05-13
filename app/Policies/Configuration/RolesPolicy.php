<?php

namespace App\Policies\Configuration;

use App\Models\Configuration\Role;
use App\Models\User;

class RolesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Role $role): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Role $role): bool
    {
        return true;
    }

    public function delete(User $user, Role $role): bool
    {
        return true;
    }

    public function restore(User $user, Role $role): bool
    {
        return true;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return true;
    }
}
