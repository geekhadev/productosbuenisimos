<?php

namespace App\Policies\Administration;

use App\Models\Administration\Module;
use App\Models\User;

class ModulesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Module $module): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Module $module): bool
    {
        return true;
    }

    public function delete(User $user, Module $module): bool
    {
        return true;
    }

    public function restore(User $user, Module $module): bool
    {
        return true;
    }

    public function forceDelete(User $user, Module $module): bool
    {
        return true;
    }
}
