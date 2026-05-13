<?php

namespace App\Actions\Configuration\Users;

use App\Models\User;

class UpdateUserBasicInfoAction
{
    public function execute(User $user, string $name, string $email): void
    {
        $user->update([
            'name' => $name,
            'email' => $email,
        ]);
    }
}
