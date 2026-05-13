<?php

namespace App\Http\Responses;

use App\Actions\Configuration\Companies\ListSelectableCompaniesForUserAction;
use App\Actions\Configuration\Companies\SetSelectedCompanyAction;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function __construct(
        private SetSelectedCompanyAction $setSelected,
        private ListSelectableCompaniesForUserAction $listSelectable,
    ) {}

    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $user = $request->user();

        if ($user instanceof User) {
            $companies = $this->listSelectable->execute($user);

            if ($companies->count() === 1) {
                $company = $companies->first();
                if ($company instanceof Company) {
                    $this->setSelected->execute($request, $user, $company);
                }
            } elseif ($companies->count() > 1) {
                return redirect()->intended(route('company-selection.index'));
            }
        }

        return redirect()->intended(Fortify::redirects('register'));
    }
}
