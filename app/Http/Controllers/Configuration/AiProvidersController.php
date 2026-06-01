<?php

namespace App\Http\Controllers\Configuration;

use App\Actions\Configuration\AiProviders\UpdateAiProviderCredentialAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\UpdateAiProviderCredentialRequest;
use App\Models\Configuration\AiProviderCredential;
use App\Models\User;
use App\Support\AiConfigurationBridge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiProvidersController extends Controller
{
    public function edit(Request $request): Response
    {
        $this->authorize('view', AiProviderCredential::class);

        $user = $request->user();
        assert($user instanceof User);

        return Inertia::render('configuration/ai-providers/edit', [
            'providers' => AiConfigurationBridge::providersForFrontend(),
            'can' => [
                'update' => $user->can('update', AiProviderCredential::class),
            ],
        ]);
    }

    public function updateCredential(
        UpdateAiProviderCredentialRequest $request,
        string $provider,
        UpdateAiProviderCredentialAction $action,
    ): RedirectResponse {
        $this->authorize('update', AiProviderCredential::class);

        abort_unless(
            in_array($provider, AiProviderCredential::manageableProviderSlugs(), true),
            404,
        );

        $action->execute($provider, $request->key());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Credencial guardada.']);

        return to_route('configuration.ai-providers.edit');
    }
}
