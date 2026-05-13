<?php

namespace Database\Seeders\Administration;

use App\Models\Administration\Module;
use App\Models\Administration\Permission;
use App\Models\Administration\System;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions_crud = [
            ['permission_name' => 'Listar', 'permission_slug' => 'list'],
            ['permission_name' => 'Crear', 'permission_slug' => 'create'],
            ['permission_name' => 'Actualizar', 'permission_slug' => 'update'],
            ['permission_name' => 'Eliminar', 'permission_slug' => 'delete'],
        ];

        $structure = [
            [
                'system_name' => 'Administración',
                'system_slug' => 'administration',
                'modules' => [
                    [
                        'module_name' => 'Sistemas',
                        'module_slug' => 'systems',
                        'permissions' => $permissions_crud,
                    ],
                    [
                        'module_name' => 'Módulos',
                        'module_slug' => 'modules',
                        'permissions' => $permissions_crud,
                    ],
                    [
                        'module_name' => 'Permisos',
                        'module_slug' => 'permissions',
                        'permissions' => $permissions_crud,
                    ],
                ],
            ],
            [
                'system_name' => 'Configuración',
                'system_slug' => 'configuration',
                'modules' => [
                    [
                        'module_name' => 'Roles',
                        'module_slug' => 'roles',
                        'permissions' => $permissions_crud,
                    ],
                    [
                        'module_name' => 'Empresas',
                        'module_slug' => 'companies',
                        'permissions' => $permissions_crud,
                    ],
                ],
            ],
            [
                'system_name' => 'Compartido',
                'system_slug' => 'shared',
                'modules' => [
                    [
                        'module_name' => 'Países',
                        'module_slug' => 'countries',
                        'permissions' => [
                            ['permission_name' => 'Listar países', 'permission_slug' => 'list'],
                            ['permission_name' => 'Crear países', 'permission_slug' => 'create'],
                            ['permission_name' => 'Editar países', 'permission_slug' => 'edit'],
                            ['permission_name' => 'Eliminar países', 'permission_slug' => 'delete'],
                        ],
                    ],
                    [
                        'module_name' => 'Estados',
                        'module_slug' => 'states',
                        'permissions' => [
                            ['permission_name' => 'Listar estados', 'permission_slug' => 'list'],
                            ['permission_name' => 'Crear estados', 'permission_slug' => 'create'],
                            ['permission_name' => 'Editar estados', 'permission_slug' => 'edit'],
                            ['permission_name' => 'Eliminar estados', 'permission_slug' => 'delete'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($structure as $systemRow) {
            $system = System::firstOrCreate(
                ['slug' => $systemRow['system_slug']],
                ['name' => $systemRow['system_name']],
            );

            foreach ($systemRow['modules'] as $moduleRow) {
                $moduleStoredSlug = Module::composeStoredSlug($system, $moduleRow['module_slug']);

                $module = Module::firstOrCreate(
                    ['slug' => $moduleStoredSlug],
                    [
                        'name' => $moduleRow['module_name'],
                        'system_id' => $system->id,
                    ],
                );

                foreach ($moduleRow['permissions'] as $permissionRow) {
                    $permissionStoredSlug = Permission::composeStoredSlug(
                        $module,
                        $permissionRow['permission_slug'],
                    );

                    Permission::firstOrCreate(
                        ['slug' => $permissionStoredSlug],
                        [
                            'name' => $permissionRow['permission_name'],
                            'module_id' => $module->id,
                        ],
                    );
                }
            }
        }
    }
}
