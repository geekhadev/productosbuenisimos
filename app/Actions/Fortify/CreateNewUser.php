<?php

namespace App\Actions\Fortify;

use App\Actions\Authentication\RegisterOwnerWithFirstCompanyAction;
use App\Models\User;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function __construct(
        private RegisterOwnerWithFirstCompanyAction $registerOwnerWithFirstCompany,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        return $this->registerOwnerWithFirstCompany->execute($input);
    }
}
