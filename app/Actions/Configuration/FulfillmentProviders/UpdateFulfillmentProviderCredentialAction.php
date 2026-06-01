<?php

namespace App\Actions\Configuration\FulfillmentProviders;

use App\Models\Configuration\FulfillmentProviderCredential;
use App\Support\FulfillmentConfigurationBridge;

class UpdateFulfillmentProviderCredentialAction
{
    /**
     * @param  array{api_url: string, user: string, pass: ?string}  $credentials
     */
    public function execute(string $provider, array $credentials): FulfillmentProviderCredential
    {
        $existing = FulfillmentProviderCredential::findForProvider($provider);
        /** @var array<string, mixed> $existingCredentials */
        $existingCredentials = $existing?->credentials ?? [];

        $pass = filled($credentials['pass'])
            ? $credentials['pass']
            : ($existingCredentials['pass'] ?? null);

        abort_unless(is_string($pass) && filled($pass), 422);

        $storedCredentials = [
            'api_url' => $credentials['api_url'],
            'user' => $credentials['user'],
            'pass' => $pass,
        ];

        $attributes = [
            'credentials' => $storedCredentials,
            'credentials_updated_at' => now(),
        ];

        if (filled($credentials['pass'])) {
            $attributes['pass_last_chars'] = FulfillmentProviderCredential::hintFromPass($credentials['pass']);
        }

        $credential = FulfillmentProviderCredential::query()->updateOrCreate(
            ['provider' => $provider],
            $attributes,
        );

        FulfillmentConfigurationBridge::apply();

        return $credential;
    }
}
