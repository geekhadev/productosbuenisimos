<?php

namespace App\Http\Controllers;

use App\Actions\Configuration\Companies\ListSelectableCompaniesForUserAction;
use App\Actions\Configuration\Companies\SetSelectedCompanyAction;
use App\Http\Requests\CompanySelectionStoreRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySelectionController extends Controller
{
    public function index(Request $request, ListSelectableCompaniesForUserAction $listAction): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $companies = $listAction->execute($user);

        if ($companies->count() === 1) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('company-selection', [
            'companies' => $companies->map(function (Company $company): array {
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
                ];
            })->values()->all(),
        ]);
    }

    public function store(
        CompanySelectionStoreRequest $request,
        SetSelectedCompanyAction $setSelected,
    ): RedirectResponse {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $company = Company::query()->findOrFail($request->validated('company_id'));

        $setSelected->execute($request, $user, $company);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Empresa cambiada correctamente.',
        ]);

        return redirect()->intended(route('dashboard'));
    }
}
