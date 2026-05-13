<?php

namespace App\Models\Administration;

use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'system_id'])]
class Module extends Model
{
    protected $table = 'administration_module';

    public static function composeStoredSlug(System $system, string $segment): string
    {
        return $system->slug.'.'.$segment;
    }

    /**
     * Obtiene el segmento de slug enviado por el usuario a partir del valor persistido y el slug del sistema.
     */
    public static function segmentFromStoredSlug(string $storedSlug, string $systemSlug): string
    {
        $prefix = $systemSlug.'.';

        if (str_starts_with($storedSlug, $prefix)) {
            return substr($storedSlug, strlen($prefix));
        }

        return $storedSlug;
    }

    /** @use HasFactory<ModuleFactory> */
    use HasFactory, HasUuids;

    public const SORTABLE_COLUMNS = [
        'name',
        'slug',
        'system_id',
        'created_at',
    ];

    /**
     * @return BelongsTo<System, $this>
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    /**
     * @return HasMany<Permission, $this>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSearchNameOrSlug(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

        return $query->where(function (Builder $inner) use ($term): void {
            $inner->where('name', 'like', $term)
                ->orWhere('slug', 'like', $term);
        });
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOrderByColumn(Builder $query, string $column, string $direction): Builder
    {
        return $query->orderBy($column, $direction);
    }

    protected static function newFactory(): ModuleFactory
    {
        return ModuleFactory::new();
    }
}
