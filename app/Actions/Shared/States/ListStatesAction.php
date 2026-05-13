<?php

namespace App\Actions\Shared\States;

use App\Models\Shared\State;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStatesAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], [...State::SORTABLE_COLUMNS, 'country'], true)
            ? $filters['sort']
            : 'name';
        $direction = ($filters['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = State::query()
            ->with('country:id,name')
            ->searchName($filters['search'] ?? null)
            ->orderByColumn($sort, $direction);

        $countryId = $filters['country_id'] ?? null;
        if ($countryId !== null && $countryId !== '') {
            $query->where('country_id', (int) $countryId);
        }

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }
}
