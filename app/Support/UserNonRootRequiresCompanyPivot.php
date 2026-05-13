<?php

namespace App\Support;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserCompanyRole;
use LogicException;

/**
 * RN-USER-04: usuarios {@see UserType::Owner} y {@see UserType::User} deben tener al menos una fila en
 * {@see UserCompanyRole} tras completar el flujo de alta. Llamar al final de toda transacción
 * que cree o deje activo un usuario de esos tipos.
 */
final class UserNonRootRequiresCompanyPivot
{
    public static function assertSatisfied(User $user): void
    {
        if (! in_array($user->type, [UserType::Owner, UserType::User], true)) {
            return;
        }

        if ($user->companyRoles()->exists()) {
            return;
        }

        throw new LogicException(
            'Users of type owner or user must have at least one user_company_roles row (RN-USER-04).',
        );
    }
}
