<?php

namespace App\Support;

use App\Models\Configuration\WhatsappProvider;
use App\Models\Configuration\WhatsappProviderCredential;
use App\Models\Configuration\WhatsappSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

final class WhatsappConfigurationBridge
{
    public static function providerHasAvailableCredentials(string $slug): bool
    {
        if (WhatsappProvider::findBySlug($slug) === null) {
            return false;
        }

        return WhatsappProviderCredential::findForProvider($slug)?->hasStoredCredentials() ?? false;
    }

    /**
     * @return list<string>
     */
    public static function selectableDefaultProviderSlugs(): array
    {
        return array_values(array_filter(
            WhatsappProviderCredential::manageableProviderSlugs(),
            fn (string $slug): bool => self::providerHasAvailableCredentials($slug),
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

        return $selectable[0];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function defaultProviderOptions(): array
    {
        if (! Schema::hasTable('configuration_whatsapp_providers')) {
            return [];
        }

        $labels = WhatsappProvider::query()
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
            ! Schema::hasTable('configuration_whatsapp_providers')
            || ! Schema::hasTable('configuration_whatsapp_provider_credentials')
        ) {
            return;
        }

        foreach (WhatsappProviderCredential::query()->cursor() as $credential) {
            if (WhatsappProvider::findBySlug($credential->provider) === null) {
                continue;
            }

            /** @var array<string, mixed> $values */
            $values = $credential->credentials ?? [];

            foreach ($values as $key => $value) {
                if (! is_string($key) || ! filled($value)) {
                    continue;
                }

                config(["whatsapp.{$credential->provider}.{$key}" => $value]);
            }
        }

        if (! Schema::hasTable('configuration_whatsapp_settings')) {
            return;
        }

        $resolved = self::resolveDefaultProvider(WhatsappSetting::instance()->default_provider);

        if ($resolved !== null) {
            config(['whatsapp.driver' => $resolved]);
        }
    }

    /**
     * @return list<array{
     *     slug: string,
     *     label: string,
     *     credentialsConfigured: bool,
     *     credentialsUpdatedAt: ?string,
     *     webhookUrl: ?string,
     *     fields: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         placeholder: ?string,
     *         value: ?string,
     *         hint: ?string,
     *     }>,
     * }>
     */
    public static function providersForFrontend(): array
    {
        if (! Schema::hasTable('configuration_whatsapp_providers')) {
            return [];
        }

        return WhatsappProvider::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->get()
            ->map(function (WhatsappProvider $provider): array {
                $stored = WhatsappProviderCredential::findForProvider($provider->slug);
                $hasStoredCredentials = $stored?->hasStoredCredentials() ?? false;
                /** @var array<string, mixed> $storedCredentials */
                $storedCredentials = $stored?->credentials ?? [];
                /** @var array<string, string> $secretHints */
                $secretHints = $stored?->secret_field_hints ?? [];

                $fields = [];

                foreach ($provider->fields as $fieldKey => $fieldDefinition) {
                    $isSecret = $fieldDefinition['type'] === 'secret';

                    $fields[] = [
                        'key' => $fieldKey,
                        'label' => $fieldDefinition['label'],
                        'type' => $fieldDefinition['type'],
                        'placeholder' => $fieldDefinition['placeholder'] ?? null,
                        'value' => $hasStoredCredentials && ! $isSecret
                            ? (is_string($storedCredentials[$fieldKey] ?? null) ? $storedCredentials[$fieldKey] : null)
                            : null,
                        'hint' => $hasStoredCredentials && $isSecret
                            ? ($secretHints[$fieldKey] ?? null)
                            : null,
                    ];
                }

                return [
                    'slug' => $provider->slug,
                    'label' => $provider->label,
                    'credentialsConfigured' => self::providerHasAvailableCredentials($provider->slug),
                    'credentialsUpdatedAt' => $hasStoredCredentials
                        ? $stored?->credentials_updated_at?->toIso8601String()
                        : null,
                    'webhookUrl' => filled($provider->webhook_route) && Route::has($provider->webhook_route)
                        ? route($provider->webhook_route)
                        : null,
                    'fields' => $fields,
                ];
            })
            ->all();
    }
}
