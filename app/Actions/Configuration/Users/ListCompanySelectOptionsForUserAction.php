<?php

namespace App\Actions\Configuration\Users;

use App\Actions\Configuration\Companies\ListSelectableCompaniesForUserAction;
use App\Enums\UserType;
use App\Models\User;

class ListCompanySelectOptionsForUserAction
{
    public function __construct(
        private ListSelectableCompaniesForUserAction $listSelectableCompanies,
    ) {}

    /**
     * Opciones `{ id, name }` para filtros de empresa en listados (solo Root tiene catálogo global).
     *
     * @return list<array{id: string, name: string}>
     */
    public function execute(User $actor): array
    {
        if ($actor->type !== UserType::Root) {
            return [];
        }

        return $this->listSelectableCompanies->execute($actor)
            ->map(fn ($company): array => [
                'id' => $company->id,
                'name' => $company->name,
            ])
            ->values()
            ->all();
    }
}
