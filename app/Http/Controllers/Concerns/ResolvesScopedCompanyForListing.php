<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\UserType;
use App\Models\User;
use App\Support\SelectedCompanySession;
use Illuminate\Http\Request;

trait ResolvesScopedCompanyForListing
{
    /**
     * Empresa de contexto para listados: null si el actor es Root (sin acotar por sesión);
     * en caso contrario, el id de empresa seleccionada en sesión.
     */
    protected function resolveScopedCompanyIdForListing(User $actor, Request $request): ?string
    {
        if ($actor->type === UserType::Root) {
            return null;
        }

        return SelectedCompanySession::selectedCompanyId($request);
    }
}
