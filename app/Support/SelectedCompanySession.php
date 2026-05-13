<?php

namespace App\Support;

use App\Actions\Configuration\Companies\ListSelectableCompaniesForUserAction;
use App\Actions\Configuration\Companies\SetSelectedCompanyAction;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

final class SelectedCompanySession
{
    public const ATTRIBUTE_KEY = 'selectable_companies_resolved';

    /**
     * Auto-asigna empresa única y guarda la colección en el request para reutilizar.
     */
    public static function synchronize(Request $request): void
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return;
        }

        $list = app(ListSelectableCompaniesForUserAction::class)->execute($user);
        $request->attributes->set(self::ATTRIBUTE_KEY, $list);

        if ($list->isEmpty()) {
            $request->session()->forget('company_selected');

            return;
        }

        $currentId = data_get($request->session()->get('company_selected'), 'id');

        if (is_string($currentId) && $currentId !== '' && ! $list->contains('id', $currentId)) {
            $request->session()->forget('company_selected');
        }

        if ($list->count() !== 1) {
            return;
        }

        $company = $list->first();
        assert($company instanceof Company);

        $currentId = data_get($request->session()->get('company_selected'), 'id');

        if ($currentId === $company->id) {
            return;
        }

        app(SetSelectedCompanyAction::class)->execute($request, $user, $company);
    }

    /**
     * @return EloquentCollection<int, Company>
     */
    public static function selectableCompaniesOrResolve(Request $request): EloquentCollection
    {
        $cached = $request->attributes->get(self::ATTRIBUTE_KEY);

        if ($cached instanceof EloquentCollection) {
            return $cached;
        }

        $user = $request->user();

        if (! $user instanceof User) {
            return new EloquentCollection;
        }

        $list = app(ListSelectableCompaniesForUserAction::class)->execute($user);
        $request->attributes->set(self::ATTRIBUTE_KEY, $list);

        return $list;
    }

    public static function selectedCompanyId(Request $request): ?string
    {
        $raw = data_get($request->session()->get('company_selected'), 'id');

        return is_string($raw) && $raw !== '' ? $raw : null;
    }

    /**
     * Empresa de contexto para la matriz de roles (solo sesión explícita).
     */
    public static function matrixCompanyId(Request $request): ?string
    {
        return self::selectedCompanyId($request);
    }
}
