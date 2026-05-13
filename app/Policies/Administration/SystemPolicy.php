<?php

namespace App\Policies\Administration;

use App\Models\Administration\System;
use App\Models\User;

class SystemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, System $system): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, System $system): bool
    {
        return true;
    }

    public function delete(User $user, System $system): bool
    {
        return true;
    }

    public function restore(User $user, System $system): bool
    {
        return true;
    }

    public function forceDelete(User $user, System $system): bool
    {
        return true;
    }
}
