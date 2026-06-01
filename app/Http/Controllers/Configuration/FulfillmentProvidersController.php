<?php

namespace App\Http\Controllers\Configuration;

use App\Actions\Configuration\FulfillmentProviders\UpdateFulfillmentProviderCredentialAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\UpdateFulfillmentProviderCredentialRequest;
use App\Models\Configuration\FulfillmentProviderCredential;
use App\Models\User;
use App\Support\FulfillmentConfigurationBridge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FulfillmentProvidersController extends Controller
{
    public function edit(Request $request): Response
    {
        $this->authorize('view', FulfillmentProviderCredential::class);

        $user = $request->user();
        assert($user instanceof User);

        return Inertia::render('configuration/fulfillment-providers/edit', [
            'providers' => FulfillmentConfigurationBridge::providersForFrontend(),
            'can' => [
                'update' => $user->can('update', FulfillmentProviderCredential::class),
            ],
        ]);
    }

    public function updateCredential(
        UpdateFulfillmentProviderCredentialRequest $request,
        string $provider,
        UpdateFulfillmentProviderCredentialAction $action,
    ): RedirectResponse {
        $this->authorize('update', FulfillmentProviderCredential::class);

        abort_unless(
            in_array($provider, FulfillmentProviderCredential::manageableProviderSlugs(), true),
            404,
        );

        $action->execute($provider, $request->credentials());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Credencial guardada.']);

        return to_route('configuration.fulfillment-providers.edit');
    }
}
