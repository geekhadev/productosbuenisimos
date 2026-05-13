<?php

namespace App\Actions\Shared\Countries;

use App\Models\Shared\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCountriesAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], Country::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'name';
        $direction = ($filters['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        return Country::query()
            ->searchFields($filters['search'] ?? null)
            ->orderByColumn($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }
}
