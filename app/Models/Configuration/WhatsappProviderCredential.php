<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'credentials',
    'credentials_updated_at',
    'secret_field_hints',
])]
class WhatsappProviderCredential extends Model
{
    public const SECRET_HINT_VISIBLE_CHARS = 4;

    use HasUuids;

    protected $table = 'configuration_whatsapp_provider_credentials';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'credentials_updated_at' => 'datetime',
            'secret_field_hints' => 'array',
        ];
    }

    public static function hintFromSecret(string $secret): string
    {
        $trimmed = trim($secret);
        $length = strlen($trimmed);

        if ($length === 0) {
            return '';
        }

        $suffixLength = min(self::SECRET_HINT_VISIBLE_CHARS, $length);

        return '···'.substr($trimmed, -$suffixLength);
    }

    /**
     * @return list<string>
     */
    public static function manageableProviderSlugs(): array
    {
        /** @var array<string, mixed> $providers */
        $providers = config('whatsapp-providers-admin.providers', []);

        return array_keys($providers);
    }

    /**
     * @return array<string, array{label: string, type: string, placeholder?: string}>
     */
    public static function fieldDefinitionsForProvider(string $provider): array
    {
        /** @var array<string, array{label: string, type: string, placeholder?: string}> $fields */
        $fields = config("whatsapp-providers-admin.providers.{$provider}.fields", []);

        return $fields;
    }

    public static function findForProvider(string $provider): ?static
    {
        return static::query()->where('provider', $provider)->first();
    }

    public function hasStoredCredentials(): bool
    {
        /** @var array<string, mixed> $credentials */
        $credentials = $this->credentials ?? [];

        foreach (self::fieldDefinitionsForProvider($this->provider) as $field => $definition) {
            if (! filled($credentials[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
