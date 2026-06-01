<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Driver
    |--------------------------------------------------------------------------
    |
    | Driver activo para envío y recepción de mensajes WhatsApp.
    | Valores soportados: twilio, meta
    |
    */

    'driver' => env('WHATSAPP_DRIVER', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Providers
    |--------------------------------------------------------------------------
    |
    | Credenciales por proveedor. Los valores por defecto provienen del entorno;
    | el panel de configuración puede sobrescribirlos en runtime.
    |
    */

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
    ],

    'meta' => [
        'access_token' => env('META_WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
        'verify_token' => env('META_WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('META_WHATSAPP_APP_SECRET'),
    ],

];
