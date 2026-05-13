<?php

namespace App\Actions\Configuration\Companies;

use App\Models\Company;
use App\Models\Configuration\Role;
use App\Models\User;
use App\Models\UserCompanyRole;

class DeleteCompanyAction
{
    /**
     * @return string|null Mensaje de bloqueo para mostrar al usuario, o null si se puede eliminar.
     */
    public function deletionBlockedReason(User $user, ?string $sessionSelectedCompanyId, Company $company): ?string
    {
        if (! $user->isOwnerOf($company)) {
            return 'No puedes eliminar esta empresa.';
        }

        $ownerRole = Role::query()->systemOwner()->firstOrFail();

        $ownedCount = UserCompanyRole::query()
            ->where('user_id', $user->id)
            ->where('role_id', $ownerRole->id)
            ->count();

        if ($ownedCount <= 1) {
            return 'No puedes eliminar la última empresa. Debes conservar al menos una.';
        }

        if (is_string($sessionSelectedCompanyId) && $sessionSelectedCompanyId !== '' && $sessionSelectedCompanyId === $company->id) {
            return 'No puedes eliminar la empresa seleccionada. Cambia de empresa en el encabezado e inténtalo de nuevo.';
        }

        $distinctUsersOnCompany = UserCompanyRole::query()
            ->where('company_id', $company->id)
            ->pluck('user_id')
            ->unique()
            ->count();

        if ($distinctUsersOnCompany > 1) {
            return 'No puedes eliminar esta empresa porque tiene otros usuarios con roles asignados. Retira esas asignaciones antes de continuar.';
        }

        return null;
    }

    /**
     * @return string|null Mensaje de error para el usuario, o null si se eliminó correctamente.
     */
    public function execute(User $user, ?string $sessionSelectedCompanyId, Company $company): ?string
    {
        if (($reason = $this->deletionBlockedReason($user, $sessionSelectedCompanyId, $company)) !== null) {
            return $reason;
        }

        $company->delete();

        return null;
    }
}
