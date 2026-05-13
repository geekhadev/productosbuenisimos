<?php

namespace App\Actions\Configuration\Users;

use App\Models\User;
use App\Models\UserCompanyRole;
use DomainException;

class AttachUserCompanyRoleAction
{
    public function __construct(
        private ListAssignableRolesForCompanyAction $assignableRoles,
    ) {}

    public function execute(User $target, string $companyId, string $roleId): void
    {
        $allowed = $this->assignableRoles->execute($companyId)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        if (! in_array($roleId, $allowed, true)) {
            throw new DomainException('invalid_role');
        }

        if (UserCompanyRole::query()
            ->where('user_id', $target->id)
            ->where('company_id', $companyId)
            ->where('role_id', $roleId)
            ->exists()) {
            throw new DomainException('duplicate');
        }

        UserCompanyRole::query()->create([
            'user_id' => $target->id,
            'company_id' => $companyId,
            'role_id' => $roleId,
        ]);
    }
}
