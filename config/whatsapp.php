<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp
    |--------------------------------------------------------------------------
    |
    | El driver activo y las credenciales por proveedor se resuelven desde la
    | base de datos (configuration_whatsapp_*). Aquí solo quedan ajustes de
    | infraestructura no administrables en el panel.
    |
    */

    'verify_webhook' => env('WHATSAPP_VERIFY_WEBHOOK', true),

];
