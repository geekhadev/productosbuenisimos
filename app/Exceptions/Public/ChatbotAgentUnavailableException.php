<?php

namespace App\Exceptions\Public;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class ChatbotAgentUnavailableException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('El asistente de ventas no está configurado. Contacta al administrador.');
    }

    public static function requestFailed(): self
    {
        return new self('El asistente no está disponible en este momento. Intenta más tarde.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 503);
    }
}
