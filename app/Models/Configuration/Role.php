<?php

namespace App\Models\Configuration;

use App\Models\Administration\Permission;
use App\Models\Company;
use Database\Factories\Configuration\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'is_public', 'company_id'])]
class Role extends Model
{
    protected $table = 'configuration_roles';

    /** Nombre del rol público de sistema que representa titularidad en empresa (pivot). */
    public const OWNER_SYSTEM_NAME = 'Owner';

    /** @use HasFactory<RoleFactory> */
    use HasFactory, HasUuids;

    public const SORTABLE_COLUMNS = [
        'name',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'company_id' => 'string',
        ];
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForCompany(Builder $query, string $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_public', false);
    }

    /**
     * Roles públicos sin empresa (incluye {@see self::OWNER_SYSTEM_NAME}).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePublicGlobal(Builder $query): Builder
    {
        return $query->where('is_public', true)->whereNull('company_id');
    }

    /**
     * Rol público de sistema Owner (titular por empresa vía `user_company_roles`).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSystemOwner(Builder $query): Builder
    {
        return $query->publicGlobal()->where('name', self::OWNER_SYSTEM_NAME);
    }

    /**
     * Roles públicos globales editables en la matriz de permisos (excluye Owner).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePublicGlobalEditable(Builder $query): Builder
    {
        return $query->publicGlobal()->where('name', '!=', self::OWNER_SYSTEM_NAME);
    }

    /**
     * Misma restricción que {@see scopePublicGlobalEditable()} para subconsultas de validación (`Rule::exists`, etc.).
     *
     * @param  \Illuminate\Database\Query\Builder|Builder  $query
     */
    public static function constraintPublicGlobalEditable($query): void
    {
        $query->where('is_public', true)
            ->whereNull('company_id')
            ->where('name', '!=', self::OWNER_SYSTEM_NAME);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSearchName(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

        return $query->where('name', 'like', $term);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOrderByColumn(Builder $query, string $column, string $direction): Builder
    {
        return $query->orderBy($column, $direction);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'configuration_role_permission',
            'configuration_role_id',
            'permission_id',
        )->withTimestamps();
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
