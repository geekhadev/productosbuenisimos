<?php

namespace App\Policies\Shared;

use App\Models\Shared\Country;
use App\Models\User;

class CountriesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Country $country): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Country $country): bool
    {
        return true;
    }

    public function delete(User $user, Country $country): bool
    {
        return true;
    }

    public function restore(User $user, Country $country): bool
    {
        return true;
    }

    public function forceDelete(User $user, Country $country): bool
    {
        return true;
    }
}
