<?php

namespace App\Actions\Administration\Permissions;

use App\Models\Administration\Module;
use App\Models\Administration\Permission;

class CreatePermissionAction
{
    /**
     * @param  array{name: string, slug: string, module_id: string}  $data  `slug` = segmento (sin prefijo de módulo)
     */
    public function execute(array $data): Permission
    {
        $module = Module::query()->findOrFail($data['module_id']);
        $storedSlug = Permission::composeStoredSlug($module, $data['slug']);

        return Permission::query()->create([
            'name' => $data['name'],
            'slug' => $storedSlug,
            'module_id' => $data['module_id'],
        ]);
    }
}
