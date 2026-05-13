<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Depuración CAF / DTE boleta (solo desarrollo)
    |--------------------------------------------------------------------------
    |
    | Por defecto desactivado. No uses en producción: el SII puede rechazar
    | envíos si el CAF no supera la verificación criptográfica.
    |
    */
    'sii_boleta_dte_debug' => [
        'skip_caf_cryptographic_verification' => env('SII_BOLETA_SKIP_CAF_VERIFY', false),
    ],

];
