<?php

namespace App\Actions\Configuration\Users;

use App\Models\Configuration\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListAssignableRolesForCompanyAction
{
    /**
     * Roles privados de la empresa + roles públicos globales, excluyendo titularidad
     * Owner de sistema y cualquier rol cuyo nombre sea «root» (asignación solo a usuarios operativos).
     *
     * @return Collection<int, Role>
     */
    public function execute(string $companyId): Collection
    {
        $table = (new Role)->getTable();

        return Role::query()
            ->where(function (Builder $query) use ($companyId): void {
                $query->where(function (Builder $q) use ($companyId): void {
                    $q->forCompany($companyId)->private();
                })->orWhere(fn (Builder $q) => $q->publicGlobal());
            })
            ->whereRaw("LOWER({$table}.name) NOT IN (?, ?)", ['owner', 'root'])
            ->orderBy('name')
            ->get();
    }
}
