<?php

namespace App\Models;

use App\Exceptions\DuplicateCompanyOwnerException;
use App\Models\Configuration\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'company_id', 'role_id'])]
class UserCompanyRole extends Model
{
    protected $table = 'user_company_roles';

    protected static function booted(): void
    {
        static::saving(function (UserCompanyRole $pivot): void {
            $ownerRoleId = Role::query()->systemOwner()->value('id');

            if ($ownerRoleId === null || (string) $pivot->role_id !== (string) $ownerRoleId) {
                return;
            }

            $query = static::query()
                ->where('company_id', $pivot->company_id)
                ->where('role_id', $ownerRoleId);

            if ($pivot->exists) {
                $query->whereKeyNot($pivot->getKey());
            }

            if ($query->exists()) {
                throw DuplicateCompanyOwnerException::forCompany();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
