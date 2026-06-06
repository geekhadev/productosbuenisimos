<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'credentials',
    'key_updated_at',
    'key_last_chars',
])]
class AiProviderCredential extends Model
{
    public const KEY_HINT_VISIBLE_CHARS = 4;

    use HasUuids;

    protected $table = 'configuration_ai_provider_credentials';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'key_updated_at' => 'datetime',
        ];
    }

    public static function hintFromKey(string $key): string
    {
        $trimmed = trim($key);
        $length = strlen($trimmed);

        if ($length === 0) {
            return '';
        }

        $suffixLength = min(self::KEY_HINT_VISIBLE_CHARS, $length);

        return '···'.substr($trimmed, -$suffixLength);
    }

    /**
     * @return list<string>
     */
    public static function manageableProviderSlugs(): array
    {
        return AiProvider::manageableSlugs();
    }

    public static function findForProvider(string $provider): ?static
    {
        return static::query()->where('provider', $provider)->first();
    }

    public function hasStoredKey(): bool
    {
        /** @var array<string, mixed> $credentials */
        $credentials = $this->credentials ?? [];
        $fieldDefinitions = AiProvider::fieldDefinitionsForProvider($this->provider);

        if ($fieldDefinitions === []) {
            return false;
        }

        foreach ($fieldDefinitions as $field => $definition) {
            unset($definition);

            if (! filled($credentials[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
