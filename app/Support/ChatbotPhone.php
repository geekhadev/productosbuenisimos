<?php

namespace App\Support;

final class ChatbotPhone
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '56') && strlen($digits) > 9) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0') && strlen($digits) > 9) {
            $digits = ltrim($digits, '0');
        }

        return $digits;
    }

    /**
     * Reconstruye el número E.164 completo a partir del número normalizado.
     * Invierte la operación de normalize() para obtener el formato que espera Twilio.
     */
    public static function toE164(string $normalizedPhone): string
    {
        return '+56'.$normalizedPhone;
    }

    public static function participantUserId(string $normalizedPhone): int
    {
        return (int) $normalizedPhone;
    }
}
