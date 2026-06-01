<?php

namespace App\Support;

use App\Models\Configuration\FulfillmentProviderCredential;
use Illuminate\Support\Facades\Schema;

final class FulfillmentConfigurationBridge
{
    /**
     * @return list<string>
     */
    private static function requiredCredentialFields(): array
    {
        return ['api_url', 'user', 'pass'];
    }

    public static function providerHasAvailableCredentials(string $slug): bool
    {
        if (! array_key_exists($slug, config('fulfillment.providers', []))) {
            return false;
        }

        return FulfillmentProviderCredential::findForProvider($slug)?->hasStoredCredentials() ?? false;
    }

    public static function apply(): void
    {
        if (! Schema::hasTable('configuration_fulfillment_provider_credentials')) {
            return;
        }

        foreach (FulfillmentProviderCredential::query()->cursor() as $credential) {
            /** @var array<string, mixed> $values */
            $values = $credential->credentials ?? [];

            foreach ($values as $key => $value) {
                if (! is_string($key) || ! filled($value)) {
                    continue;
                }

                config(["fulfillment.providers.{$credential->provider}.{$key}" => $value]);
            }
        }
    }

    /**
     * @return list<array{
     *     slug: string,
     *     label: string,
     *     credentialsConfigured: bool,
     *     credentialsUpdatedAt: ?string,
     *     apiUrl: ?string,
     *     user: ?string,
     *     passLastChars: ?string,
     *     configuredViaEnvironment: bool,
     * }>
     */
    public static function providersForFrontend(): array
    {
        /** @var array<string, array{label: string, fields: array<string, array{label: string, type: string}>}> $catalog */
        $catalog = config('fulfillment-providers-admin.providers', []);

        return array_map(
            function (array $definition, string $slug): array {
                $stored = FulfillmentProviderCredential::findForProvider($slug);
                $hasStoredCredentials = $stored?->hasStoredCredentials() ?? false;
                $configuredViaEnvironment = self::providerConfiguredViaEnvironment($slug) && ! $hasStoredCredentials;
                /** @var array<string, mixed> $storedCredentials */
                $storedCredentials = $stored?->credentials ?? [];

                return [
                    'slug' => $slug,
                    'label' => $definition['label'],
                    'credentialsConfigured' => self::providerHasAvailableCredentials($slug),
                    'credentialsUpdatedAt' => $hasStoredCredentials
                        ? $stored?->credentials_updated_at?->toIso8601String()
                        : null,
                    'apiUrl' => $hasStoredCredentials
                        ? ($storedCredentials['api_url'] ?? null)
                        : null,
                    'user' => $hasStoredCredentials
                        ? ($storedCredentials['user'] ?? null)
                        : null,
                    'passLastChars' => $hasStoredCredentials
                        ? $stored?->pass_last_chars
                        : null,
                    'configuredViaEnvironment' => $configuredViaEnvironment,
                ];
            },
            $catalog,
            array_keys($catalog),
        );
    }

    private static function providerConfiguredViaEnvironment(string $slug): bool
    {
        foreach (self::requiredCredentialFields() as $field) {
            if (! filled(config("fulfillment.providers.{$slug}.{$field}"))) {
                return false;
            }
        }

        return true;
    }
}
