<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'credentials',
    'credentials_updated_at',
    'pass_last_chars',
])]
class FulfillmentProviderCredential extends Model
{
    public const PASS_HINT_VISIBLE_CHARS = 4;

    use HasUuids;

    protected $table = 'configuration_fulfillment_provider_credentials';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'credentials_updated_at' => 'datetime',
        ];
    }

    public static function hintFromPass(string $pass): string
    {
        $trimmed = trim($pass);
        $length = strlen($trimmed);

        if ($length === 0) {
            return '';
        }

        $suffixLength = min(self::PASS_HINT_VISIBLE_CHARS, $length);

        return '···'.substr($trimmed, -$suffixLength);
    }

    /**
     * @return list<string>
     */
    public static function manageableProviderSlugs(): array
    {
        return FulfillmentProvider::manageableSlugs();
    }

    public static function findForProvider(string $provider): ?static
    {
        return static::query()->where('provider', $provider)->first();
    }

    public function hasStoredCredentials(): bool
    {
        /** @var array<string, mixed> $credentials */
        $credentials = $this->credentials ?? [];
        $fieldDefinitions = FulfillmentProvider::fieldDefinitionsForProvider($this->provider);

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
