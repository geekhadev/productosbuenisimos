<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;

#[Fillable([
    'slug',
    'label',
    'fields',
    'is_active',
])]
class FulfillmentProvider extends Model
{
    use HasUuids;

    protected $table = 'configuration_fulfillment_providers';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return list<string>
     */
    public static function manageableSlugs(): array
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return [];
        }

        return static::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->pluck('slug')
            ->all();
    }

    /**
     * @return array<string, array{label: string, type: string}>
     */
    public static function fieldDefinitionsForProvider(string $slug): array
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return [];
        }

        /** @var array<string, array{label: string, type: string}> $fields */
        $fields = static::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->value('fields') ?? [];

        return $fields;
    }

    public static function findBySlug(string $slug): ?static
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return null;
        }

        return static::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function credential(): HasOne
    {
        return $this->hasOne(FulfillmentProviderCredential::class, 'provider', 'slug');
    }
}
