<?php

namespace App\Actions\Configuration\Users;

use App\Enums\UserType;
use App\Models\User;

class CreateUserAction
{
    /**
     * @param  array{name: string, email: string, type: UserType, password: string}  $data
     */
    public function execute(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'type' => $data['type'],
            'password' => $data['password'],
        ]);
    }
}
