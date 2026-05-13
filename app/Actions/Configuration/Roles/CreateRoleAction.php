<?php

namespace App\Actions\Configuration\Roles;

use App\Models\Configuration\Role;

class CreateRoleAction
{
    /**
     * @param  array{name: string, company_id: string, permission_ids: list<string>}  $data
     */
    public function execute(array $data): Role
    {
        $role = Role::query()->create([
            'name' => $data['name'],
            'is_public' => false,
            'company_id' => $data['company_id'],
        ]);

        $role->permissions()->sync($data['permission_ids']);

        return $role;
    }
}
