<?php

namespace App\Http\Controllers\Configuration;

use App\Actions\Configuration\WhatsappProviders\UpdateWhatsappProviderCredentialAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\UpdateWhatsappProviderCredentialRequest;
use App\Models\Configuration\WhatsappProviderCredential;
use App\Models\User;
use App\Support\WhatsappConfigurationBridge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WhatsappProvidersController extends Controller
{
    public function edit(Request $request): Response
    {
        $this->authorize('view', WhatsappProviderCredential::class);

        $user = $request->user();
        assert($user instanceof User);

        return Inertia::render('configuration/whatsapp-providers/edit', [
            'providers' => WhatsappConfigurationBridge::providersForFrontend(),
            'can' => [
                'update' => $user->can('update', WhatsappProviderCredential::class),
            ],
        ]);
    }

    public function updateCredential(
        UpdateWhatsappProviderCredentialRequest $request,
        string $provider,
        UpdateWhatsappProviderCredentialAction $action,
    ): RedirectResponse {
        $this->authorize('update', WhatsappProviderCredential::class);

        abort_unless(
            in_array($provider, WhatsappProviderCredential::manageableProviderSlugs(), true),
            404,
        );

        $action->execute($provider, $request->credentials());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Credencial guardada.']);

        return to_route('configuration.whatsapp-providers.edit');
    }
}
