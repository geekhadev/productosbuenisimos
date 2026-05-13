<?php

namespace App\Actions\Configuration\Roles;

use App\Models\Administration\System;

class BuildPermissionsTreeForFormAction
{
    /**
     * @return list<array{id: string, name: string, slug: string, modules: list<array{id: string, name: string, slug: string, permissions: list<array{id: string, name: string, slug: string}>}>}>
     */
    public function execute(): array
    {
        $systems = System::query()
            ->with([
                'modules' => fn ($query) => $query->orderBy('name'),
                'modules.permissions' => fn ($query) => $query->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        $tree = [];

        foreach ($systems as $system) {
            $modulesPayload = [];

            foreach ($system->modules as $module) {
                $permissionsPayload = [];

                foreach ($module->permissions as $permission) {
                    $permissionsPayload[] = [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'slug' => $permission->slug,
                    ];
                }

                $modulesPayload[] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'permissions' => $permissionsPayload,
                ];
            }

            $tree[] = [
                'id' => $system->id,
                'name' => $system->name,
                'slug' => $system->slug,
                'modules' => $modulesPayload,
            ];
        }

        return $tree;
    }
}
