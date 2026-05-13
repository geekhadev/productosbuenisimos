<?php

namespace App\Policies\Shared;

use App\Models\Shared\State;
use App\Models\User;

class StatesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, State $state): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, State $state): bool
    {
        return true;
    }

    public function delete(User $user, State $state): bool
    {
        return true;
    }

    public function restore(User $user, State $state): bool
    {
        return true;
    }

    public function forceDelete(User $user, State $state): bool
    {
        return true;
    }
}
