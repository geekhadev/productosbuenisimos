<?php

namespace App\Models;

use App\Enums\CompanyDocumentType;
use App\Models\Configuration\Role;
use App\Models\Stock\Product;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Empresa de configuración. En base de datos, `document_type`, `document_number` y `name` son NOT NULL.
 */
#[Fillable([
    'document_type',
    'document_number',
    'name',
    'alias',
    'email',
    'phone',
    'address',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasUuids;

    protected $table = 'configuration_companies';

    /**
     * @return HasMany<UserCompanyRole, $this>
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class, 'company_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_company_roles', 'company_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function owner(): ?User
    {
        $ownerRole = Role::query()->systemOwner()->first();

        if (! $ownerRole) {
            return null;
        }

        $pivot = UserCompanyRole::query()
            ->where('company_id', $this->id)
            ->where('role_id', $ownerRole->id)
            ->first();

        return $pivot?->user;
    }

    /**
     * @return HasMany<Role, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * @return HasMany<CompanyIntegrationSetting, $this>
     */
    public function integrationSettings(): HasMany
    {
        return $this->hasMany(CompanyIntegrationSetting::class, 'company_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_type' => CompanyDocumentType::class,
        ];
    }
}
