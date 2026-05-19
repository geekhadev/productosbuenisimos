<?php

namespace App\Actions\Sales\Orders;

use App\Models\Sales\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListOrdersAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(string $companyId, array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], Order::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        return Order::query()
            ->where('company_id', $companyId)
            ->withCount('items')
            ->with(['customer', 'address'])
            ->searchFields($filters['search'] ?? null)
            ->createdBetween(
                $filters['date_from'] ?? null,
                $filters['date_to'] ?? null,
            )
            ->orderByColumn($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }
}
