<?php

namespace App\Actions\Sales\Leads;

use App\Models\Sales\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListLeadsAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(string $companyId, array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], Lead::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        return Lead::query()
            ->where('company_id', $companyId)
            ->with('customer')
            ->searchFields($filters['search'] ?? null)
            ->withStatus(is_string($filters['status'] ?? null) ? $filters['status'] : null)
            ->withSource(is_string($filters['source'] ?? null) ? $filters['source'] : null)
            ->orderByColumn($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }
}
