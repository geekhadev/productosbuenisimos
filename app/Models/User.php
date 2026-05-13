<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserType;
use App\Models\Configuration\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'type', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, TwoFactorAuthenticatable;

    public const SORTABLE_COLUMNS = [
        'name',
        'email',
        'type',
        'created_at',
    ];

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSearchNameOrEmail(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

        return $query->where(function (Builder $inner) use ($term): void {
            $inner->where('name', 'like', $term)
                ->orWhere('email', 'like', $term);
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'type' => UserType::class,
        ];
    }

    /**
     * @return HasMany<UserCompanyRole, $this>
     */
    public function companyRoles(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class);
    }

    /**
     * @return BelongsToMany<Company, $this>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_company_roles', 'user_id', 'company_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function isOwnerOf(Company $company): bool
    {
        $ownerRole = Role::query()->systemOwner()->first();

        if (! $ownerRole) {
            return false;
        }

        return $this->companyRoles()
            ->where('company_id', $company->id)
            ->where('role_id', $ownerRole->id)
            ->exists();
    }

    /**
     * Whether the user holds the public Owner role alone for at least one company in `user_company_roles`.
     */
    public function isSoleOwnerOfAnyCompany(): bool
    {
        $ownerRoleId = Role::query()->systemOwner()->value('id');

        if ($ownerRoleId === null) {
            return false;
        }

        return UserCompanyRole::query()
            ->where('user_id', $this->id)
            ->where('role_id', $ownerRoleId)
            ->whereRaw(
                '(select count(*) from user_company_roles ucr_owner where ucr_owner.company_id = user_company_roles.company_id and ucr_owner.role_id = ?) = 1',
                [$ownerRoleId],
            )
            ->exists();
    }
}
