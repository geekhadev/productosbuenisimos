<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fulfillment Providers
    |--------------------------------------------------------------------------
    |
    | Credenciales de proveedores de fulfillment. Los valores por defecto provienen
    | del entorno; el panel de configuración puede sobrescribirlos en runtime.
    |
    */

    'providers' => [
        'contraentrega' => [
            'api_url' => env('CONTRAENTREGA_API_URL'),
            'user' => env('CONTRAENTREGA_USER'),
            'pass' => env('CONTRAENTREGA_PASS'),
        ],
    ],

];
