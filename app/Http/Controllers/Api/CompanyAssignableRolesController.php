<?php

namespace App\Http\Controllers\Api;

use App\Actions\Configuration\Users\ListAssignableRolesForCompanyAction;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyAssignableRolesController extends Controller
{
    public function __invoke(
        Request $request,
        Company $company,
        ListAssignableRolesForCompanyAction $action,
    ): JsonResponse {
        $this->authorize('viewRolesForUserAssignment', $company);

        $roles = $action->execute($company->id);

        $selectedIds = [];
        $assigneeId = $request->query('assigned_for_user');

        if (is_string($assigneeId) && $assigneeId !== '') {
            $assignee = User::query()->findOrFail($assigneeId);
            $this->authorize('syncCompanyRoles', $assignee);
            $selectedIds = $assignee->companyRoles()
                ->where('company_id', $company->id)
                ->pluck('role_id')
                ->map(fn ($id): string => (string) $id)
                ->values()
                ->all();
        }

        return response()->json([
            'data' => $roles->map(fn ($role): array => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values()->all(),
            'selected_ids' => $selectedIds,
        ]);
    }
}
