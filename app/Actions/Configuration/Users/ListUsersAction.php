<?php

namespace App\Actions\Configuration\Users;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;

class ListUsersAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(User $actor, ?string $scopedCompanyId, array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'], User::SORTABLE_COLUMNS, true)
            ? $filters['sort']
            : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($filters['per_page'] ?? 20);

        if ($actor->type !== UserType::Root) {
            if ($scopedCompanyId === null || $scopedCompanyId === '') {
                return new ConcretePaginator([], 0, max(1, $perPage), 1, [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]);
            }

            return $this->paginateForOwner($actor, $scopedCompanyId, $filters, $sort, $direction, $perPage);
        }

        return $this->paginateForRoot($actor, $filters, $sort, $direction, $perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paginateForRoot(User $actor, array $filters, string $sort, string $direction, int $perPage): LengthAwarePaginator
    {
        $query = User::query()
            ->where('type', '!=', UserType::Root)
            ->where('id', '!=', $actor->id)
            ->searchNameOrEmail($filters['search'] ?? null)
            ->orderByColumn($sort, $direction);

        $type = $filters['type'] ?? null;
        if (is_string($type) && $type !== '') {
            $query->where('type', $type);
        }

        $companyId = $filters['company_id'] ?? null;
        if (is_string($companyId) && $companyId !== '') {
            $query->whereHas('companyRoles', fn ($q) => $q->where('company_id', $companyId));
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->getCollection()->transform(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type->value,
            'created_at' => $user->created_at?->toIso8601String() ?? '',
        ]);

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paginateForOwner(
        User $actor,
        string $companyId,
        array $filters,
        string $sort,
        string $direction,
        int $perPage,
    ): LengthAwarePaginator {
        $query = User::query()
            ->where('type', '!=', UserType::Root)
            ->where('id', '!=', $actor->id)
            ->whereHas('companyRoles', fn ($q) => $q->where('company_id', $companyId))
            ->with([
                'companyRoles' => fn ($q) => $q->where('company_id', $companyId)->with('role:id,name'),
            ])
            ->searchNameOrEmail($filters['search'] ?? null)
            ->orderByColumn($sort, $direction);

        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->getCollection()->transform(function (User $user): array {
            $roles = $user->companyRoles
                ->pluck('role.name')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
            ];
        });

        return $paginator;
    }
}
