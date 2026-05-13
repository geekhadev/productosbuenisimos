<?php

namespace App\Http\Controllers\Configuration;

use App\Actions\Configuration\Companies\CreateCompanyAction;
use App\Actions\Configuration\Companies\DeleteCompanyAction;
use App\Actions\Configuration\Companies\ListCompaniesAction;
use App\Actions\Configuration\Companies\UpdateCompanyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\CompaniesRequest;
use App\Http\Requests\Configuration\CompanyListRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompaniesController extends Controller
{
    public function index(CompanyListRequest $request, ListCompaniesAction $action, DeleteCompanyAction $deleteCompanyAction): Response
    {
        $this->authorize('viewAny', Company::class);

        $user = $request->user();
        $selectedFromSession = data_get($request->session()->get('company_selected'), 'id');
        $sessionSelectedCompanyId = is_string($selectedFromSession) ? $selectedFromSession : null;

        $companies = $action->execute($user)->map(function (Company $company) use ($user, $deleteCompanyAction, $sessionSelectedCompanyId): array {
            $ownerUser = $company->owner();

            return [
                'id' => $company->id,
                'document_type' => $company->document_type->value,
                'document_number' => $company->document_number,
                'name' => $company->name,
                'alias' => $company->alias,
                'email' => $company->email,
                'phone' => $company->phone,
                'address' => $company->address,
                'owner' => $ownerUser ? [
                    'id' => $ownerUser->id,
                    'name' => $ownerUser->name,
                    'email' => $ownerUser->email,
                ] : null,
                'can' => [
                    'update' => $user->can('update', $company),
                    'delete' => $user->can('delete', $company)
                        && $deleteCompanyAction->deletionBlockedReason($user, $sessionSelectedCompanyId, $company) === null,
                ],
            ];
        })->values()->all();

        return Inertia::render('configuration/companies/index', [
            'companies' => $companies,
            'can' => [
                'create' => $user->can('create', Company::class),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Company::class);

        return Inertia::render('configuration/companies/form', [
            'company' => null,
            'can' => [
                'delete' => false,
            ],
        ]);
    }

    public function store(CompaniesRequest $request, CreateCompanyAction $action): RedirectResponse
    {
        $company = $action->execute($request->user(), $request->companyPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Empresa creada.']);

        return to_route('configuration.companies.edit', $company);
    }

    public function edit(Request $request, Company $company, DeleteCompanyAction $deleteCompanyAction): Response
    {
        $this->authorize('update', $company);

        $user = $request->user();
        $sessionSelectedCompanyId = data_get($request->session()->get('company_selected'), 'id');
        $sessionSelectedCompanyId = is_string($sessionSelectedCompanyId) ? $sessionSelectedCompanyId : null;

        return Inertia::render('configuration/companies/form', [
            'company' => $this->companyFormProps($company),
            'can' => [
                'delete' => $user->can('delete', $company)
                    && $deleteCompanyAction->deletionBlockedReason($user, $sessionSelectedCompanyId, $company) === null,
            ],
        ]);
    }

    public function update(CompaniesRequest $request, Company $company, UpdateCompanyAction $action): RedirectResponse
    {
        $action->execute($company, $request->companyPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Empresa actualizada.']);

        return to_route('configuration.companies.edit', $company);
    }

    public function destroy(Request $request, Company $company, DeleteCompanyAction $action): RedirectResponse
    {
        $this->authorize('delete', $company);

        $user = $request->user();
        $sessionSelectedCompanyId = data_get($request->session()->get('company_selected'), 'id');
        $sessionSelectedCompanyId = is_string($sessionSelectedCompanyId) ? $sessionSelectedCompanyId : null;

        $errorMessage = $action->execute($user, $sessionSelectedCompanyId, $company);

        if ($errorMessage !== null) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $errorMessage]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Empresa eliminada.']);

        return to_route('configuration.companies.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function companyFormProps(Company $company): array
    {
        return [
            'id' => $company->id,
            'document_type' => $company->document_type->value,
            'document_number' => $company->document_number,
            'name' => $company->name,
            'alias' => $company->alias,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
        ];
    }
}
