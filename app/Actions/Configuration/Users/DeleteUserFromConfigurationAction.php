<?php

namespace App\Actions\Configuration\Users;

use App\Models\User;

class DeleteUserFromConfigurationAction
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
