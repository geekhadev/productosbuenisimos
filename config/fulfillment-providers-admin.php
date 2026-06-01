<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proveedores de fulfillment administrables desde Configuración
    |--------------------------------------------------------------------------
    |
    | Catálogo de proveedores de fulfillment que se pueden configurar en el panel.
    | Las claves de cada campo deben coincidir con config/fulfillment.php → providers.*.
    |
    */

    'providers' => [
        'contraentrega' => [
            'label' => 'CONTRAENTREGA',
            'fields' => [
                'api_url' => ['label' => 'URL API', 'type' => 'text'],
                'user' => ['label' => 'Usuario', 'type' => 'text'],
                'pass' => ['label' => 'Contraseña', 'type' => 'secret'],
            ],
        ],
    ],

];
