<?php

namespace App\Policies\Configuration;

use App\Enums\UserType;
use App\Models\Company;
use App\Models\User;

class CompaniesPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->type, [UserType::Root, UserType::Owner], true);
    }

    public function view(User $user, Company $company): bool
    {
        return $this->manages($user, $company);
    }

    /**
     * Listar roles asignables a usuarios en una empresa (modal empresa-roles).
     */
    public function viewRolesForUserAssignment(User $user, Company $company): bool
    {
        if ($user->type === UserType::Root) {
            return true;
        }

        return $user->companyRoles()->where('company_id', $company->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->type, [UserType::Root, UserType::Owner], true);
    }

    public function update(User $user, Company $company): bool
    {
        return $this->manages($user, $company);
    }

    public function delete(User $user, Company $company): bool
    {
        return $this->manages($user, $company);
    }

    public function restore(User $user, Company $company): bool
    {
        return $this->manages($user, $company);
    }

    public function forceDelete(User $user, Company $company): bool
    {
        return $this->manages($user, $company);
    }

    private function manages(User $user, Company $company): bool
    {
        if ($user->type === UserType::Root) {
            return true;
        }

        return $user->isOwnerOf($company);
    }
}
