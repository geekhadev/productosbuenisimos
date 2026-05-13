<?php

namespace App\Actions\Configuration\Roles;

use App\Models\Configuration\Role;

class DeleteRoleAction
{
    public function execute(Role $role): void
    {
        $role->delete();
    }
}
