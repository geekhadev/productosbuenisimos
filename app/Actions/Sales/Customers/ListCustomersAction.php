<?php

namespace App\Actions\Sales\Customers;

use App\Models\Sales\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomersAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(string $companyId, array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], Customer::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'full_name';
        $direction = ($filters['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        return Customer::query()
            ->where('company_id', $companyId)
            ->searchFields($filters['search'] ?? null)
            ->orderByColumn($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }
}
