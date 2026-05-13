<?php

namespace App\Actions\Configuration\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SetSelectedCompanyAction
{
    public function __construct(
        private ListSelectableCompaniesForUserAction $listSelectable,
    ) {}

    /**
     * Persiste la empresa seleccionada en sesión como arreglo serializable.
     */
    public function execute(Request $request, User $user, Company $company): void
    {
        $allowedIds = $this->listSelectable->execute($user)->pluck('id');

        if (! $allowedIds->contains($company->id)) {
            abort(403, 'No autorizado para usar esta empresa.');
        }

        $previousId = data_get($request->session()->get('company_selected'), 'id');

        if ($previousId !== $company->id) {
            $this->forgetCompanyContextSessionKeys($request);
        }

        $request->session()->put('company_selected', $this->toSessionArray($company));
    }

    private function forgetCompanyContextSessionKeys(Request $request): void
    {
        /** @var Collection<int, string> $keys */
        $keys = collect($request->session()->all())
            ->keys()
            ->filter(fn (mixed $key) => is_string($key) && (
                $key === 'company_ctx' || str_starts_with($key, 'company_ctx.')
            ))
            ->values();

        if ($keys->isNotEmpty()) {
            $request->session()->forget($keys->all());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toSessionArray(Company $company): array
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
