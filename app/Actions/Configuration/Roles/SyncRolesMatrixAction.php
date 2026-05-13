<?php

namespace App\Actions\Configuration\Roles;

use App\Models\Configuration\Role;
use Illuminate\Support\Facades\DB;

class SyncRolesMatrixAction
{
    /**
     * @param  list<array{id: string, name: string, permission_ids: list<string>}>  $rows
     */
    public function execute(string $companyId, array $rows, bool $userCanEditPublicRoles = false): void
    {
        DB::transaction(function () use ($companyId, $rows, $userCanEditPublicRoles): void {
            foreach ($rows as $row) {
                $role = Role::query()->whereKey($row['id'])->firstOrFail();

                if ($role->is_public && $role->company_id === null && $role->name === Role::OWNER_SYSTEM_NAME) {
                    abort(404);
                }

                if ($role->is_public && $role->company_id === null) {
                    abort_unless($userCanEditPublicRoles, 404);
                } elseif ($role->company_id !== $companyId || $role->is_public) {
                    abort(404);
                }

                $role->update([
                    'name' => $row['name'],
                ]);

                $role->permissions()->sync($row['permission_ids']);
            }
        });
    }
}
