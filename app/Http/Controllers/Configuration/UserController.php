<?php

namespace App\Http\Controllers\Configuration;

use App\Actions\Configuration\Users\CreateUserAction;
use App\Actions\Configuration\Users\DeleteUserFromConfigurationAction;
use App\Actions\Configuration\Users\ListCompanySelectOptionsForUserAction;
use App\Actions\Configuration\Users\ListUsersAction;
use App\Actions\Configuration\Users\UpdateUserBasicInfoAction;
use App\Enums\UserType;
use App\Http\Controllers\Concerns\ResolvesScopedCompanyForListing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\DestroyUserRequest;
use App\Http\Requests\Configuration\StoreUserRequest;
use App\Http\Requests\Configuration\UpdateUserRequest;
use App\Http\Requests\Configuration\UserListRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use ResolvesScopedCompanyForListing;

    public function index(
        UserListRequest $request,
        ListUsersAction $action,
        ListCompanySelectOptionsForUserAction $companySelectOptions,
    ): Response {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $scopedCompanyId = $this->resolveScopedCompanyIdForListing($actor, $request);

        $users = $action->execute($actor, $scopedCompanyId, $request->filtersForAction());

        $companies = $companySelectOptions->execute($actor);

        return Inertia::render('configuration/users/index', [
            'data' => $users,
            'filters' => $request->filtersForFrontend(),
            'listMode' => $actor->type === UserType::Root ? 'root' : 'owner',
            'companies' => $companies,
        ]);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        $this->authorize('create', User::class);

        $action->execute($request->payload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario creado.']);

        return to_route('configuration.users.index');
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserBasicInfoAction $action,
    ): RedirectResponse {
        $action->execute($user, $request->name(), $request->email());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario actualizado.']);

        return to_route('configuration.users.index');
    }

    public function destroy(
        DestroyUserRequest $request,
        User $user,
        DeleteUserFromConfigurationAction $action,
    ): RedirectResponse {
        $action->execute($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario eliminado.']);

        return to_route('configuration.users.index');
    }
}
