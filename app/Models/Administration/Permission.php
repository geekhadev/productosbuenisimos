<?php

namespace App\Models\Administration;

use Database\Factories\Administration\PermissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'slug', 'module_id'])]
class Permission extends Model
{
    protected $table = 'administration_permissions';

    public static function composeStoredSlug(Module $module, string $segment): string
    {
        return $module->slug.'.'.$segment;
    }

    /**
     * Obtiene el segmento de slug enviado por el usuario a partir del valor persistido y el slug del módulo.
     */
    public static function segmentFromStoredSlug(string $storedSlug, string $moduleSlug): string
    {
        $prefix = $moduleSlug.'.';

        if (str_starts_with($storedSlug, $prefix)) {
            return substr($storedSlug, strlen($prefix));
        }

        return $storedSlug;
    }

    /** @use HasFactory<PermissionFactory> */
    use HasFactory, HasUuids;

    public const SORTABLE_COLUMNS = [
        'name',
        'slug',
        'module_id',
        'created_at',
    ];

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
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

    protected static function newFactory(): PermissionFactory
    {
        return PermissionFactory::new();
    }
}
