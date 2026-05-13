<?php

namespace App\Actions\Administration\Permissions;

use App\Models\Administration\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPermissionsAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], Permission::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = Permission::query()
            ->with([
                'module:id,name,slug,system_id',
                'module.system:id,name,slug',
            ])
            ->searchNameOrSlug($filters['search'] ?? null)
            ->orderByColumn($sort, $direction);

        $moduleId = $filters['module_id'] ?? null;
        if ($moduleId !== null && $moduleId !== '') {
            $query->where('module_id', (string) $moduleId);
        } else {
            $systemId = $filters['system_id'] ?? null;
            if ($systemId !== null && $systemId !== '') {
                $query->whereHas('module', function ($q) use ($systemId): void {
                    $q->where('system_id', (string) $systemId);
                });
            }
        }

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }
}
