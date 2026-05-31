<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proveedores y modelos del agente de ventas
    |--------------------------------------------------------------------------
    |
    | Catálogo de proveedores de IA y los modelos permitidos por cada uno.
    | Se usa en la configuración del agente (UI y validación) y en runtime.
    |
    */

    'providers' => [
        'openai' => [
            'label' => 'OpenAI',
            'models' => [
                [
                    'value' => 'gpt-4o-mini',
                    'label' => 'GPT-4o Mini',
                ],
            ],
        ],
    ],

];
