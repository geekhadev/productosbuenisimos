<?php

namespace Database\Seeders\Sales;

use App\Models\Company;
use App\Models\Sales\ConversationTag;
use Illuminate\Database\Seeder;

class ConversationTagSeeder extends Seeder
{
    /**
     * @var list<array{name: string, description: string, color: string, sort_order: int}>
     */
    private const TAGS = [
        [
            'sort_order' => 1,
            'name' => 'Contacto iniciado',
            'description' => 'El cliente inició la conversación y respondió con su nombre (Paso 1 completado).',
            'color' => 'slate-500',
        ],
        [
            'sort_order' => 2,
            'name' => 'Producto consultado',
            'description' => 'El cliente indicó qué producto le interesa y el agente lo identificó en el catálogo (Paso 2 completado).',
            'color' => 'sky-500',
        ],
        [
            'sort_order' => 3,
            'name' => 'Producto presentado',
            'description' => 'El agente presentó la descripción y video del producto al cliente (Paso 3 completado).',
            'color' => 'blue-500',
        ],
        [
            'sort_order' => 4,
            'name' => 'Interés confirmado',
            'description' => 'El cliente confirmó que desea agregar el producto a su pedido con un "sí" (Paso 4 completado).',
            'color' => 'indigo-500',
        ],
        [
            'sort_order' => 5,
            'name' => 'Dirección en proceso',
            'description' => 'El cliente comenzó a proporcionar su dirección de entrega; ya respondió al menos el estado (Paso 5 completado).',
            'color' => 'violet-500',
        ],
        [
            'sort_order' => 6,
            'name' => 'Dirección completa',
            'description' => 'El cliente proporcionó todos los datos de entrega: estado, ciudad, colonia, calle, número exterior y referencia (Pasos 5 al 10 completados).',
            'color' => 'purple-500',
        ],
        [
            'sort_order' => 7,
            'name' => 'Pedido confirmado por cliente',
            'description' => 'El cliente revisó el resumen del pedido con precio, dirección y fecha de entrega, y respondió "sí" para confirmar (Paso 11 completado).',
            'color' => 'orange-500',
        ],
        [
            'sort_order' => 8,
            'name' => 'Pedido registrado',
            'description' => 'El pedido fue creado exitosamente en el sistema y se envió al cliente el número de pedido de confirmación (Paso 12 completado).',
            'color' => 'green-500',
        ],
        [
            'sort_order' => 9,
            'name' => 'Venta cruzada ofrecida',
            'description' => 'El agente presentó productos adicionales al cliente después de registrar el pedido principal (Paso 13 completado).',
            'color' => 'amber-500',
        ],
    ];

    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            foreach (self::TAGS as $tag) {
                ConversationTag::query()->firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'sort_order' => $tag['sort_order'],
                    ],
                    [
                        'name' => $tag['name'],
                        'description' => $tag['description'],
                        'color' => $tag['color'],
                    ],
                );
            }
        }
    }
}
