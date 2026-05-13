<?php

namespace App\Http\Controllers\Api;

use App\Actions\Configuration\Users\AttachUserCompanyRoleAction;
use App\Actions\Configuration\Users\DetachUserCompanyRoleAction;
use App\Actions\Configuration\Users\ListUserCompanyRoleAssignmentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\AttachUserCompanyRoleRequest;
use App\Models\User;
use App\Models\UserCompanyRole;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCompanyRoleAssignmentsController extends Controller
{
    public function index(
        Request $request,
        User $user,
        ListUserCompanyRoleAssignmentsAction $action,
    ): JsonResponse {
        $this->authorize('syncCompanyRoles', $user);

        return response()->json([
            'data' => $action->execute($request, $request->user(), $user),
        ]);
    }

    public function store(
        AttachUserCompanyRoleRequest $request,
        User $user,
        AttachUserCompanyRoleAction $attach,
        ListUserCompanyRoleAssignmentsAction $list,
    ): JsonResponse {
        $this->authorize('syncCompanyRoles', $user);

        try {
            $attach->execute($user, $request->companyId(), $request->roleId());
        } catch (DomainException $e) {
            if ($e->getMessage() === 'duplicate') {
                return response()->json([
                    'message' => 'El usuario ya tiene ese rol en la empresa.',
                ], 422);
            }

            if ($e->getMessage() === 'invalid_role') {
                return response()->json([
                    'message' => 'El rol no es válido para la empresa seleccionada.',
                ], 422);
            }

            throw $e;
        }

        return response()->json([
            'data' => $list->execute($request, $request->user(), $user),
        ], 201);
    }

    public function destroy(
        Request $request,
        User $user,
        string $assignment,
        DetachUserCompanyRoleAction $detach,
        ListUserCompanyRoleAssignmentsAction $list,
    ): JsonResponse {
        $this->authorize('syncCompanyRoles', $user);

        $pivot = UserCompanyRole::query()
            ->where('user_id', $user->id)
            ->whereKey($assignment)
            ->firstOrFail();

        $detach->execute($request, $request->user(), $user, $pivot);

        return response()->json([
            'data' => $list->execute($request, $request->user(), $user),
        ]);
    }
}
