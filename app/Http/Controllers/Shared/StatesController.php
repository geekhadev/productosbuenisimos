<?php

namespace App\Http\Controllers\Shared;

use App\Actions\Shared\States\CreateStateAction;
use App\Actions\Shared\States\DeleteStateAction;
use App\Actions\Shared\States\ListStatesAction;
use App\Actions\Shared\States\UpdateStateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\StateListRequest;
use App\Http\Requests\Shared\StateStoreRequest;
use App\Http\Requests\Shared\StateUpdateRequest;
use App\Models\Shared\Country;
use App\Models\Shared\State;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StatesController extends Controller
{
    public function index(StateListRequest $request, ListStatesAction $action): Response
    {
        $this->authorize('viewAny', State::class);

        $states = $action->execute($request->filtersForAction());
        $countries = Country::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('shared/states/index', [
            'data' => $states,
            'filters' => $request->filtersForFrontend(),
            'countries' => $countries,
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', State::class);

        return to_route('shared.states.index');
    }

    public function store(StateStoreRequest $request, CreateStateAction $action): RedirectResponse
    {
        $this->authorize('create', State::class);

        $action->execute($request->statePayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('State created.')]);

        return to_route('shared.states.index');
    }

    public function edit(State $state): RedirectResponse
    {
        $this->authorize('update', $state);

        return to_route('shared.states.index');
    }

    public function update(StateUpdateRequest $request, State $state, UpdateStateAction $action): RedirectResponse
    {
        $this->authorize('update', $state);

        $action->execute($state, $request->statePayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('State updated.')]);

        return to_route('shared.states.index');
    }

    public function destroy(State $state, DeleteStateAction $action): RedirectResponse
    {
        $this->authorize('delete', $state);

        $action->execute($state);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('State deleted.')]);

        return to_route('shared.states.index');
    }
}
