<?php

namespace App\Actions\Configuration\AiProviders;

use App\Models\Configuration\AiProviderCredential;
use App\Support\AiConfigurationBridge;

class UpdateAiProviderCredentialAction
{
    public function execute(string $provider, string $key): AiProviderCredential
    {
        $credential = AiProviderCredential::query()->updateOrCreate(
            ['provider' => $provider],
            [
                'credentials' => ['key' => $key],
                'key_last_chars' => AiProviderCredential::hintFromKey($key),
                'key_updated_at' => now(),
            ],
        );

        AiConfigurationBridge::apply();

        return $credential;
    }
}
