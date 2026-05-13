<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Administration\Permissions\CreatePermissionAction;
use App\Actions\Administration\Permissions\DeletePermissionAction;
use App\Actions\Administration\Permissions\ListPermissionsAction;
use App\Actions\Administration\Permissions\UpdatePermissionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\PermissionListRequest;
use App\Http\Requests\Administration\PermissionsRequest;
use App\Models\Administration\Module;
use App\Models\Administration\Permission;
use App\Models\Administration\System;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PermissionsController extends Controller
{
    public function index(PermissionListRequest $request, ListPermissionsAction $action): Response
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = $action->execute($request->filtersForAction());

        return Inertia::render('administration/permissions/index', [
            'data' => $permissions,
            'filters' => $request->filtersForFrontend(),
            ...$this->dependencies(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', Permission::class);

        return to_route('administration.permissions.index');
    }

    public function store(PermissionsRequest $request, CreatePermissionAction $action): RedirectResponse
    {
        $this->authorize('create', Permission::class);

        $action->execute($request->permissionPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Permission created.')]);

        return to_route('administration.permissions.index');
    }

    public function edit(Permission $permission): RedirectResponse
    {
        $this->authorize('update', $permission);

        return to_route('administration.permissions.index');
    }

    public function update(PermissionsRequest $request, Permission $permission, UpdatePermissionAction $action): RedirectResponse
    {
        $this->authorize('update', $permission);

        $action->execute($permission, $request->permissionPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Permission updated.')]);

        return to_route('administration.permissions.index');
    }

    public function destroy(Permission $permission, DeletePermissionAction $action): RedirectResponse
    {
        $this->authorize('delete', $permission);

        $action->execute($permission);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Permission deleted.')]);

        return to_route('administration.permissions.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function dependencies(): array
    {
        return [
            'systems' => System::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'modules' => Module::query()
                ->with('system:id,name,slug')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'system_id']),
        ];
    }
}
