<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Administration\Systems\CreateSystemAction;
use App\Actions\Administration\Systems\DeleteSystemAction;
use App\Actions\Administration\Systems\ListSystemsAction;
use App\Actions\Administration\Systems\UpdateSystemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\SystemListRequest;
use App\Http\Requests\Administration\SystemsRequest;
use App\Models\Administration\System;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SystemsController extends Controller
{
    public function index(SystemListRequest $request, ListSystemsAction $action): Response
    {
        $this->authorize('viewAny', System::class);

        $systems = $action->execute($request->filtersForAction());

        return Inertia::render('administration/systems/index', [
            'data' => $systems,
            'filters' => $request->filtersForFrontend(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', System::class);

        return to_route('administration.systems.index');
    }

    public function store(SystemsRequest $request, CreateSystemAction $action): RedirectResponse
    {
        $this->authorize('create', System::class);

        $action->execute($request->systemPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sistema creado.']);

        return to_route('administration.systems.index');
    }

    public function edit(System $system): RedirectResponse
    {
        $this->authorize('update', $system);

        return to_route('administration.systems.index');
    }

    public function update(SystemsRequest $request, System $system, UpdateSystemAction $action): RedirectResponse
    {
        $this->authorize('update', $system);

        $action->execute($system, $request->systemPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sistema actualizado.']);

        return to_route('administration.systems.index');
    }

    public function destroy(System $system, DeleteSystemAction $action): RedirectResponse
    {
        $this->authorize('delete', $system);

        $action->execute($system);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sistema eliminado.']);

        return to_route('administration.systems.index');
    }
}
