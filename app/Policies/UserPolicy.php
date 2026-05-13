<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\User;
use App\Support\SelectedCompanySession;

/**
 * Autorización sobre el modelo {@see User}.
 *
 * El atributo `type` no es un campo de negocio editable por formularios genéricos:
 * cambiarlo implica migración explícita del pivot `user_company_roles` y solo debe
 * hacerse en flujos controlados (fuera del perfil / mass assignment habitual).
 */
class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->type, [UserType::Root, UserType::Owner], true);
    }

    /**
     * Alta de usuario desde administración (solo Root; Owner usa flujo de invitación).
     */
    public function create(User $actor): bool
    {
        return $actor->type === UserType::Root;
    }

    /**
     * Actualizar nombre y correo desde administración de usuarios (Root / Owner en contexto empresa).
     */
    public function update(User $actor, User $target): bool
    {
        if ($target->type === UserType::Root) {
            return false;
        }

        if ($actor->type === UserType::Root) {
            return true;
        }

        if ($actor->type !== UserType::Owner) {
            return false;
        }

        $companyId = SelectedCompanySession::selectedCompanyId(request());

        if (! is_string($companyId) || $companyId === '') {
            return false;
        }

        $actorInCompany = $actor->companyRoles()->where('company_id', $companyId)->exists();
        $targetInCompany = $target->companyRoles()->where('company_id', $companyId)->exists();

        return $actorInCompany && $targetInCompany;
    }

    /**
     * Eliminar usuario desde administración (no aplica borrado de la propia sesión ni cuentas root).
     */
    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        if ($target->type === UserType::Root) {
            return false;
        }

        if ($actor->type === UserType::Root) {
            return true;
        }

        if ($actor->type !== UserType::Owner) {
            return false;
        }

        $companyId = SelectedCompanySession::selectedCompanyId(request());

        if (! is_string($companyId) || $companyId === '') {
            return false;
        }

        $actorInCompany = $actor->companyRoles()->where('company_id', $companyId)->exists();
        $targetInCompany = $target->companyRoles()->where('company_id', $companyId)->exists();

        return $actorInCompany && $targetInCompany;
    }

    /**
     * Sincronizar roles del usuario en una empresa (modal empresa-roles).
     */
    public function syncCompanyRoles(User $actor, User $target): bool
    {
        if ($target->type === UserType::Root) {
            return false;
        }

        if ($actor->type === UserType::Root) {
            return true;
        }

        if ($actor->type !== UserType::Owner) {
            return false;
        }

        $companyId = SelectedCompanySession::selectedCompanyId(request());

        if (! is_string($companyId) || $companyId === '') {
            return false;
        }

        $actorInCompany = $actor->companyRoles()->where('company_id', $companyId)->exists();
        $targetInCompany = $target->companyRoles()->where('company_id', $companyId)->exists();

        return $actorInCompany && $targetInCompany;
    }

    /**
     * Actualizar datos de perfil (nombre, email, etc.). No incluye cambio de `type`.
     */
    public function updateProfile(User $actor, User $target): bool
    {
        return $actor->id === $target->id;
    }
}
