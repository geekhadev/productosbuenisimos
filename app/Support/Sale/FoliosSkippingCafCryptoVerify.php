<?php

namespace App\Support\Sale;

use sasco\LibreDTE\Sii\Folios;

/**
 * Extiende {@see Folios} para omitir la verificación criptográfica del CAF (firma SII y prueba RSA)
 * cuando {@see config('sale.sii_boleta_dte_debug.skip_caf_cryptographic_verification')} es true.
 */
final class FoliosSkippingCafCryptoVerify extends Folios
{
    public function check(): bool
    {
        if (config('sale.sii_boleta_dte_debug.skip_caf_cryptographic_verification', false)) {
            return true;
        }

        return parent::check();
    }
}
