<?php

namespace App\Actions\Administration\Modules;

use App\Models\Administration\Module;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListModulesAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], Module::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = Module::query()
            ->with(['system:id,name,slug'])
            ->searchNameOrSlug($filters['search'] ?? null)
            ->orderByColumn($sort, $direction);

        $systemId = $filters['system_id'] ?? null;
        if ($systemId !== null && $systemId !== '') {
            $query->where('system_id', (string) $systemId);
        }

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }
}
