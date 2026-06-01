<?php

namespace App\Actions\Configuration\WhatsappProviders;

use App\Models\Configuration\WhatsappProviderCredential;
use App\Support\WhatsappConfigurationBridge;

class UpdateWhatsappProviderCredentialAction
{
    /**
     * @param  array<string, ?string>  $credentials
     */
    public function execute(string $provider, array $credentials): WhatsappProviderCredential
    {
        $existing = WhatsappProviderCredential::findForProvider($provider);
        /** @var array<string, mixed> $existingCredentials */
        $existingCredentials = $existing?->credentials ?? [];
        /** @var array<string, string> $existingHints */
        $existingHints = $existing?->secret_field_hints ?? [];

        $fieldDefinitions = WhatsappProviderCredential::fieldDefinitionsForProvider($provider);
        $storedCredentials = [];
        $secretHints = $existingHints;

        foreach ($fieldDefinitions as $fieldKey => $definition) {
            $incoming = $credentials[$fieldKey] ?? null;

            $value = filled($incoming)
                ? $incoming
                : ($existingCredentials[$fieldKey] ?? null);

            abort_unless(is_string($value) && filled($value), 422);

            $storedCredentials[$fieldKey] = $value;

            if ($definition['type'] === 'secret' && filled($incoming)) {
                $secretHints[$fieldKey] = WhatsappProviderCredential::hintFromSecret($incoming);
            }
        }

        $credential = WhatsappProviderCredential::query()->updateOrCreate(
            ['provider' => $provider],
            [
                'credentials' => $storedCredentials,
                'secret_field_hints' => $secretHints,
                'credentials_updated_at' => now(),
            ],
        );

        WhatsappConfigurationBridge::apply();

        return $credential;
    }
}
