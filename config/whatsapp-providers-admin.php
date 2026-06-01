<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proveedores de WhatsApp administrables desde Configuración
    |--------------------------------------------------------------------------
    |
    | Catálogo de proveedores de WhatsApp que se pueden configurar en el panel.
    | Las claves de cada campo deben coincidir con config/whatsapp.php → {provider}.*.
    |
    */

    'providers' => [
        'twilio' => [
            'label' => 'Twilio',
            'webhook_route' => 'webhook.whatsapp.twilio',
            'fields' => [
                'account_sid' => ['label' => 'Account SID', 'type' => 'text'],
                'auth_token' => ['label' => 'Auth Token', 'type' => 'secret'],
                'from_number' => [
                    'label' => 'Número de origen',
                    'type' => 'text',
                    'placeholder' => 'whatsapp:+14155238886',
                ],
            ],
        ],
        'meta' => [
            'label' => 'Meta',
            'webhook_route' => 'webhook.whatsapp.meta',
            'fields' => [
                'access_token' => ['label' => 'Access Token', 'type' => 'secret'],
                'phone_number_id' => ['label' => 'Phone Number ID', 'type' => 'text'],
                'verify_token' => ['label' => 'Verify Token', 'type' => 'secret'],
                'app_secret' => ['label' => 'App Secret', 'type' => 'secret'],
            ],
        ],
    ],

];
