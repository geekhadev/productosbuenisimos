<?php

namespace App\Actions\Configuration\Users;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserCompanyRole;
use App\Support\SelectedCompanySession;
use Illuminate\Http\Request;

class ListUserCompanyRoleAssignmentsAction
{
    /**
     * Filas empresa–rol del usuario. Owner solo ve la empresa de contexto (sesión).
     *
     * @return list<array{id: string, company_id: string, company_name: string, role_id: string, role_name: string}>
     */
    public function execute(Request $request, User $actor, User $target): array
    {
        $query = UserCompanyRole::query()
            ->where('user_id', $target->id)
            ->with([
                'company:id,name',
                'role:id,name',
            ]);

        if ($actor->type !== UserType::Root) {
            $companyId = SelectedCompanySession::selectedCompanyId($request);

            if (is_string($companyId) && $companyId !== '') {
                $query->where('company_id', $companyId);
            }
        }

        return $query
            ->orderBy('company_id')
            ->orderBy('role_id')
            ->get()
            ->map(fn (UserCompanyRole $row): array => [
                'id' => (string) $row->getKey(),
                'company_id' => $row->company_id,
                'company_name' => $row->company?->name ?? '',
                'role_id' => $row->role_id,
                'role_name' => $row->role?->name ?? '',
            ])
            ->values()
            ->all();
    }
}
