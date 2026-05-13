<?php

namespace App\Actions\Administration\Permissions;

use App\Models\Administration\Permission;

class DeletePermissionAction
{
    public function execute(Permission $permission): void
    {
        $permission->delete();
    }
}
