<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Administration\Modules\CreateModuleAction;
use App\Actions\Administration\Modules\DeleteModuleAction;
use App\Actions\Administration\Modules\ListModulesAction;
use App\Actions\Administration\Modules\UpdateModuleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\ModuleListRequest;
use App\Http\Requests\Administration\ModulesRequest;
use App\Models\Administration\Module;
use App\Models\Administration\System;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ModulesController extends Controller
{
    public function index(ModuleListRequest $request, ListModulesAction $action): Response
    {
        $this->authorize('viewAny', Module::class);

        $modules = $action->execute($request->filtersForAction());

        return Inertia::render('administration/modules/index', [
            'data' => $modules,
            'filters' => $request->filtersForFrontend(),
            ...$this->dependencies(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', Module::class);

        return to_route('administration.modules.index');
    }

    public function store(ModulesRequest $request, CreateModuleAction $action): RedirectResponse
    {
        $this->authorize('create', Module::class);

        $action->execute($request->modulePayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Module created.')]);

        return to_route('administration.modules.index');
    }

    public function edit(Module $module): RedirectResponse
    {
        $this->authorize('update', $module);

        return to_route('administration.modules.index');
    }

    public function update(ModulesRequest $request, Module $module, UpdateModuleAction $action): RedirectResponse
    {
        $this->authorize('update', $module);

        $action->execute($module, $request->modulePayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Module updated.')]);

        return to_route('administration.modules.index');
    }

    public function destroy(Module $module, DeleteModuleAction $action): RedirectResponse
    {
        $this->authorize('delete', $module);

        $action->execute($module);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Module deleted.')]);

        return to_route('administration.modules.index');
    }

    protected function dependencies(): array
    {
        return [
            'systems' => System::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ];
    }
}
