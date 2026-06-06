<?php

namespace App\Support;

use App\Models\Configuration\AiProvider;
use App\Models\Configuration\AiProviderCredential;
use App\Models\Configuration\AiSetting;
use Illuminate\Support\Facades\Schema;

final class AiConfigurationBridge
{
    public static function providerHasAvailableKey(string $slug): bool
    {
        if (AiProvider::findBySlug($slug) === null) {
            return false;
        }

        return AiProviderCredential::findForProvider($slug)?->hasStoredKey() ?? false;
    }

    /**
     * @return list<string>
     */
    public static function availableProviderSlugs(): array
    {
        return array_values(array_filter(
            array_keys(config('ai.providers', [])),
            fn (string $slug): bool => self::providerHasAvailableKey($slug),
        ));
    }

    /**
     * @return list<string>
     */
    public static function availableSalesAgentProviderSlugs(): array
    {
        /** @var array<string, mixed> $salesAgentCatalog */
        $salesAgentCatalog = config('sales-agent.providers', []);

        return array_values(array_filter(
            array_keys($salesAgentCatalog),
            fn (string $slug): bool => self::providerHasAvailableKey($slug),
        ));
    }

    /**
     * @return list<string>
     */
    public static function selectableDefaultProviderSlugs(): array
    {
        return array_values(array_filter(
            AiProviderCredential::manageableProviderSlugs(),
            fn (string $slug): bool => self::providerHasAvailableKey($slug),
        ));
    }

    public static function resolveDefaultProvider(?string $stored = null): ?string
    {
        $selectable = self::selectableDefaultProviderSlugs();

        if ($selectable === []) {
            return null;
        }

        if ($stored !== null && in_array($stored, $selectable, true)) {
            return $stored;
        }

        $configDefault = (string) config('ai.default', 'openai');

        if (in_array($configDefault, $selectable, true)) {
            return $configDefault;
        }

        return $selectable[0];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function defaultProviderOptions(): array
    {
        if (! Schema::hasTable('configuration_ai_providers')) {
            return [];
        }

        $labels = AiProvider::query()
            ->whereIn('slug', self::selectableDefaultProviderSlugs())
            ->pluck('label', 'slug');

        return array_values(array_map(
            fn (string $slug): array => [
                'value' => $slug,
                'label' => (string) $labels->get($slug, $slug),
            ],
            self::selectableDefaultProviderSlugs(),
        ));
    }

    public static function apply(): void
    {
        if (
            ! Schema::hasTable('configuration_ai_providers')
            || ! Schema::hasTable('configuration_ai_provider_credentials')
        ) {
            return;
        }

        foreach (AiProviderCredential::query()->cursor() as $credential) {
            if (AiProvider::findBySlug($credential->provider) === null) {
                continue;
            }

            /** @var array<string, mixed> $values */
            $values = $credential->credentials ?? [];

            foreach ($values as $key => $value) {
                if (! is_string($key) || ! filled($value)) {
                    continue;
                }

                config(["ai.providers.{$credential->provider}.{$key}" => $value]);
            }
        }

        if (! Schema::hasTable('configuration_ai_settings')) {
            return;
        }

        $resolved = self::resolveDefaultProvider(AiSetting::instance()->default_provider);

        if ($resolved !== null) {
            config(['ai.default' => $resolved]);
        }
    }

    /**
     * @return list<array{
     *     slug: string,
     *     label: string,
     *     keyConfigured: bool,
     *     keyUpdatedAt: ?string,
     *     keyLastChars: ?string,
     * }>
     */
    public static function providersForFrontend(): array
    {
        if (! Schema::hasTable('configuration_ai_providers')) {
            return [];
        }

        return AiProvider::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->get()
            ->map(function (AiProvider $provider): array {
                $stored = AiProviderCredential::findForProvider($provider->slug);
                $hasStoredKey = $stored?->hasStoredKey() ?? false;

                return [
                    'slug' => $provider->slug,
                    'label' => $provider->label,
                    'keyConfigured' => self::providerHasAvailableKey($provider->slug),
                    'keyUpdatedAt' => $hasStoredKey
                        ? $stored?->key_updated_at?->toIso8601String()
                        : null,
                    'keyLastChars' => $hasStoredKey
                        ? $stored?->key_last_chars
                        : null,
                ];
            })
            ->all();
    }
}
