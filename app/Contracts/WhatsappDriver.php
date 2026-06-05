<?php

namespace App\Contracts;

use App\Whatsapp\WhatsappIncomingMessage;
use Illuminate\Http\Request;

interface WhatsappDriver
{
    /**
     * Valida que el webhook proviene del proveedor legítimo.
     * Lanza HttpException(403) si la firma no es válida.
     */
    public function verifyWebhook(Request $request): void;

    /**
     * Convierte el request del proveedor en el DTO interno.
     * Retorna null si el evento no es un mensaje entrante (ej. status update).
     */
    public function parseIncomingMessage(Request $request): ?WhatsappIncomingMessage;

    /**
     * Envía un mensaje de texto al número de destino.
     */
    public function sendTextMessage(string $to, string $body): void;

    /**
     * Envía un mensaje con media (video, imagen) al número de destino.
     */
    public function sendMediaMessage(string $to, string $mediaUrl, string $caption = ''): void;

    /**
     * Descarga el contenido binario de un archivo media del proveedor.
     * Cada driver maneja la autenticación necesaria para acceder a la URL.
     */
    public function downloadMedia(string $url): string;
}
