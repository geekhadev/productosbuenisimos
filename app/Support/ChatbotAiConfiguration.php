<?php

namespace App\Support;

final class ChatbotAiConfiguration
{
    public static function isConfigured(): bool
    {
        $provider = (string) config('ai.default', 'openai');

        return AiConfigurationBridge::providerHasAvailableKey($provider);
    }
}
