<?php

namespace App\Support;

use App\Models\Configuration\WhatsappProviderCredential;
use App\Models\Configuration\WhatsappSetting;
use Illuminate\Support\Facades\Schema;

final class WhatsappConfigurationBridge
{
    public static function providerHasAvailableCredentials(string $slug): bool
    {
        if (! array_key_exists($slug, config('whatsapp-providers-admin.providers', []))) {
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
            fn (string $slug): bool => self::providerIsRuntimeReady($slug),
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

        $configDefault = (string) config('whatsapp.driver', 'twilio');

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
        /** @var array<string, array{label: string}> $catalog */
        $catalog = config('whatsapp-providers-admin.providers', []);

        return array_values(array_map(
            fn (string $slug): array => [
                'value' => $slug,
                'label' => $catalog[$slug]['label'],
            ],
            self::selectableDefaultProviderSlugs(),
        ));
    }

    public static function apply(): void
    {
        if (! Schema::hasTable('configuration_whatsapp_provider_credentials')) {
            return;
        }

        foreach (WhatsappProviderCredential::query()->cursor() as $credential) {
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
     *     configuredViaEnvironment: bool,
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
        /** @var array<string, array{label: string, fields: array<string, array{label: string, type: string, placeholder?: string}>}> $catalog */
        $catalog = config('whatsapp-providers-admin.providers', []);

        return array_map(
            function (array $definition, string $slug): array {
                $stored = WhatsappProviderCredential::findForProvider($slug);
                $hasStoredCredentials = $stored?->hasStoredCredentials() ?? false;
                $configuredViaEnvironment = self::providerConfiguredViaEnvironment($slug) && ! $hasStoredCredentials;
                /** @var array<string, mixed> $storedCredentials */
                $storedCredentials = $stored?->credentials ?? [];
                /** @var array<string, string> $secretHints */
                $secretHints = $stored?->secret_field_hints ?? [];

                $fields = [];

                foreach ($definition['fields'] as $fieldKey => $fieldDefinition) {
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
                    'slug' => $slug,
                    'label' => $definition['label'],
                    'credentialsConfigured' => self::providerHasAvailableCredentials($slug),
                    'credentialsUpdatedAt' => $hasStoredCredentials
                        ? $stored?->credentials_updated_at?->toIso8601String()
                        : null,
                    'configuredViaEnvironment' => $configuredViaEnvironment,
                    'fields' => $fields,
                ];
            },
            $catalog,
            array_keys($catalog),
        );
    }

    private static function providerIsRuntimeReady(string $slug): bool
    {
        foreach (WhatsappProviderCredential::fieldDefinitionsForProvider($slug) as $field => $definition) {
            unset($definition);

            if (! filled(config("whatsapp.{$slug}.{$field}"))) {
                return false;
            }
        }

        return true;
    }

    private static function providerConfiguredViaEnvironment(string $slug): bool
    {
        return self::providerIsRuntimeReady($slug);
    }
}
