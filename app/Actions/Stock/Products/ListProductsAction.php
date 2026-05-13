<?php

namespace App\Actions\Stock\Products;

use App\Models\Stock\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(string $companyId, array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], Product::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'name';
        $direction = ($filters['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);
        $status = $filters['status'] ?? null;

        return Product::query()
            ->where('company_id', $companyId)
            ->searchFields($filters['search'] ?? null)
            ->activeStatus(is_string($status) ? $status : null)
            ->orderByColumn($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }
}
