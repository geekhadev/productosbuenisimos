<?php

namespace App\Actions\Configuration\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListCompaniesAction
{
    public function __construct(
        private ListSelectableCompaniesForUserAction $listSelectable,
    ) {}

    /**
     * @return Collection<int, Company>
     */
    public function execute(User $user): Collection
    {
        return $this->listSelectable->execute($user);
    }
}
