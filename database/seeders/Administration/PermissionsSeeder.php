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
                    [
                        'module_name' => 'Proveedores de IA',
                        'module_slug' => 'ai-providers',
                        'permissions' => [
                            ['permission_name' => 'Listar proveedores de IA', 'permission_slug' => 'list'],
                            ['permission_name' => 'Actualizar proveedores de IA', 'permission_slug' => 'update'],
                        ],
                    ],
                    [
                        'module_name' => 'Proveedores de fulfillment',
                        'module_slug' => 'fulfillment-providers',
                        'permissions' => [
                            ['permission_name' => 'Listar proveedores de fulfillment', 'permission_slug' => 'list'],
                            ['permission_name' => 'Actualizar proveedores de fulfillment', 'permission_slug' => 'update'],
                        ],
                    ],
                    [
                        'module_name' => 'Proveedores de WhatsApp',
                        'module_slug' => 'whatsapp-providers',
                        'permissions' => [
                            ['permission_name' => 'Listar proveedores de WhatsApp', 'permission_slug' => 'list'],
                            ['permission_name' => 'Actualizar proveedores de WhatsApp', 'permission_slug' => 'update'],
                        ],
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
            [
                'system_name' => 'Stock',
                'system_slug' => 'stock',
                'modules' => [
                    [
                        'module_name' => 'Productos',
                        'module_slug' => 'products',
                        'permissions' => $permissions_crud,
                    ],
                ],
            ],
            [
                'system_name' => 'Ventas',
                'system_slug' => 'sales',
                'modules' => [
                    [
                        'module_name' => 'Clientes',
                        'module_slug' => 'customers',
                        'permissions' => $permissions_crud,
                    ],
                    [
                        'module_name' => 'Leads',
                        'module_slug' => 'leads',
                        'permissions' => $permissions_crud,
                    ],
                    [
                        'module_name' => 'Pedidos',
                        'module_slug' => 'orders',
                        'permissions' => $permissions_crud,
                    ],
                    [
                        'module_name' => 'Agente de Ventas',
                        'module_slug' => 'agent',
                        'permissions' => [
                            ['permission_name' => 'Listar Agente de Ventas', 'permission_slug' => 'list'],
                            ['permission_name' => 'Actualizar Agente de Ventas', 'permission_slug' => 'update'],
                        ],
                    ],
                    [
                        'module_name' => 'Conversaciones',
                        'module_slug' => 'conversations',
                        'permissions' => [
                            ['permission_name' => 'Listar conversaciones', 'permission_slug' => 'list'],
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
