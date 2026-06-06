<?php

namespace App\Support;

use App\Models\Configuration\FulfillmentProvider;
use App\Models\Configuration\FulfillmentProviderCredential;
use Illuminate\Support\Facades\Schema;

final class FulfillmentConfigurationBridge
{
    public static function providerHasAvailableCredentials(string $slug): bool
    {
        if (FulfillmentProvider::findBySlug($slug) === null) {
            return false;
        }

        return FulfillmentProviderCredential::findForProvider($slug)?->hasStoredCredentials() ?? false;
    }

    public static function apply(): void
    {
        if (
            ! Schema::hasTable('configuration_fulfillment_providers')
            || ! Schema::hasTable('configuration_fulfillment_provider_credentials')
        ) {
            return;
        }

        foreach (FulfillmentProviderCredential::query()->cursor() as $credential) {
            if (FulfillmentProvider::findBySlug($credential->provider) === null) {
                continue;
            }

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
     * }>
     */
    public static function providersForFrontend(): array
    {
        if (! Schema::hasTable('configuration_fulfillment_providers')) {
            return [];
        }

        return FulfillmentProvider::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->get()
            ->map(function (FulfillmentProvider $provider): array {
                $stored = FulfillmentProviderCredential::findForProvider($provider->slug);
                $hasStoredCredentials = $stored?->hasStoredCredentials() ?? false;
                /** @var array<string, mixed> $storedCredentials */
                $storedCredentials = $stored?->credentials ?? [];

                return [
                    'slug' => $provider->slug,
                    'label' => $provider->label,
                    'credentialsConfigured' => self::providerHasAvailableCredentials($provider->slug),
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
                ];
            })
            ->all();
    }
}
