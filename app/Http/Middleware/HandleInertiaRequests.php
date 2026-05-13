<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use App\Support\SelectedCompanySession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/server-side/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/server-side/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        SelectedCompanySession::synchronize($request);

        $user = $request->user();

        $companySelected = null;
        /** @var array<int, array<string, mixed>> $selectableCompanies */
        $selectableCompanies = [];
        $showCompanySwitcher = false;

        if ($user instanceof User) {
            $list = SelectedCompanySession::selectableCompaniesOrResolve($request);

            $selectableCompanies = $list
                ->map(fn (Company $company): array => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'alias' => $company->alias,
                    'document_type' => $company->document_type?->value,
                    'document_number' => $company->document_number,
                ])
                ->values()
                ->all();
            $showCompanySwitcher = $list->count() > 1;
            $companySelected = $request->session()->get('company_selected');
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $this->resolveUserPermissions($request),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'company_selected' => $companySelected,
            'selectable_companies' => $selectableCompanies,
            'show_company_switcher' => $showCompanySwitcher,
        ];
    }

    /**
     * @return list<string>
     */
    private function resolveUserPermissions(Request $request): array
    {
        $user = $request->user();

        if (! $user instanceof User || $user->type->value === 'root') {
            return [];
        }

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null) {
            return [];
        }

        /** @var Collection<int, string> $permissions */
        $permissions = $user->companyRoles()
            ->where('company_id', $companyId)
            ->with('role.permissions:id,slug')
            ->get()
            ->flatMap(function ($userCompanyRole): Collection {
                return $userCompanyRole->role?->permissions->pluck('slug') ?? collect();
            })
            ->filter(static fn (mixed $slug): bool => is_string($slug) && $slug !== '')
            ->unique()
            ->values();

        return $permissions->all();
    }
}
