<?php

namespace App\Http\Controllers\Configuration;

use App\Actions\Configuration\Roles\BuildPermissionsTreeForFormAction;
use App\Actions\Configuration\Roles\CreateRoleAction;
use App\Actions\Configuration\Roles\DeleteRoleAction;
use App\Actions\Configuration\Roles\ListRolesForMatrixAction;
use App\Actions\Configuration\Roles\SyncRolesMatrixAction;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\StoreRoleColumnRequest;
use App\Http\Requests\Configuration\SyncRolesMatrixRequest;
use App\Models\Configuration\Role;
use App\Support\SelectedCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    public function index(
        Request $request,
        BuildPermissionsTreeForFormAction $treeAction,
        ListRolesForMatrixAction $listAction,
    ): Response {
        $this->authorize('viewAny', Role::class);

        $user = $request->user();
        $companyId = SelectedCompanySession::matrixCompanyId($request);

        if ($companyId === null) {
            return Inertia::render('configuration/roles/index', [
                'permissionsTree' => [],
                'roles' => [],
                'companyMissing' => true,
                'canEditPublicRoles' => false,
            ]);
        }

        $includePublicRoles = $user->type === UserType::Root;
        $roles = $listAction->execute($companyId, $includePublicRoles);

        $rolesPayload = $roles->map(fn (Role $role): array => [
            'id' => $role->id,
            'name' => $role->name,
            'is_public' => $role->is_public,
            'permission_ids' => $role->permissions->pluck('id')->values()->all(),
        ])->values()->all();

        return Inertia::render('configuration/roles/index', [
            'permissionsTree' => $treeAction->execute(),
            'roles' => $rolesPayload,
            'companyMissing' => false,
            'canEditPublicRoles' => $includePublicRoles,
        ]);
    }

    public function store(StoreRoleColumnRequest $request, CreateRoleAction $action): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $user = $request->user();

        $companyId = SelectedCompanySession::matrixCompanyId($request);

        if ($companyId === null) {
            abort(403, 'Tu usuario no tiene una empresa asignada.');
        }

        $payload = $request->payload();

        $action->execute([
            'name' => $payload['name'],
            'company_id' => $companyId,
            'permission_ids' => $payload['permission_ids'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rol añadido.']);

        return to_route('configuration.roles.index');
    }

    public function sync(SyncRolesMatrixRequest $request, SyncRolesMatrixAction $action): RedirectResponse
    {
        $this->authorize('viewAny', Role::class);

        $user = $request->user();

        $companyId = SelectedCompanySession::matrixCompanyId($request);

        if ($companyId === null) {
            abort(403, 'Tu usuario no tiene una empresa asignada.');
        }

        $action->execute(
            $companyId,
            $request->matrixRows(),
            $user->type === UserType::Root,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permisos guardados.']);

        return to_route('configuration.roles.index');
    }

    public function destroy(Request $request, Role $role, DeleteRoleAction $action): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->ensureRoleBelongsToUserCompany($request, $role);

        $action->execute($role);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rol eliminado.']);

        return to_route('configuration.roles.index');
    }

    private function ensureRoleBelongsToUserCompany(Request $request, Role $role): void
    {
        $user = $request->user();

        if ($role->is_public && $role->company_id === null) {
            abort_unless($user->type === UserType::Root, 404);

            return;
        }

        $companyId = SelectedCompanySession::matrixCompanyId($request);

        abort_unless(
            $companyId !== null && $role->company_id === $companyId,
            404
        );
    }
}
