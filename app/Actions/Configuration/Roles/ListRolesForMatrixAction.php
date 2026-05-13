<?php

namespace App\Actions\Configuration\Roles;

use App\Models\Configuration\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListRolesForMatrixAction
{
    /**
     * @return Collection<int, Role>
     */
    public function execute(string $companyId, bool $includePublicGlobalRoles = false): Collection
    {
        return Role::query()
            ->with('permissions')
            ->where(function (Builder $query) use ($companyId, $includePublicGlobalRoles): void {
                $query->where(function (Builder $q) use ($companyId): void {
                    $q->forCompany($companyId)->private();
                });

                if ($includePublicGlobalRoles) {
                    $query->orWhere(fn (Builder $q) => $q->publicGlobalEditable());
                }
            })
            ->orderBy('name')
            ->get();
    }
}
