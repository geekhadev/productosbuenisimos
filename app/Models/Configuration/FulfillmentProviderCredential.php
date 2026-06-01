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
        /** @var array<string, mixed> $providers */
        $providers = config('fulfillment-providers-admin.providers', []);

        return array_keys($providers);
    }

    public static function findForProvider(string $provider): ?static
    {
        return static::query()->where('provider', $provider)->first();
    }

    public function hasStoredCredentials(): bool
    {
        /** @var array<string, mixed> $credentials */
        $credentials = $this->credentials ?? [];

        return filled($credentials['api_url'] ?? null)
            && filled($credentials['user'] ?? null)
            && filled($credentials['pass'] ?? null);
    }
}
