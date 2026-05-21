<?php

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\AgentConfig\UpdateAgentConfig;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\UpdateAgentConfigRequest;
use App\Models\Sales\SalesAgentConfig;
use App\Models\User;
use App\Support\SelectedCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentConfigController extends Controller
{
    public function edit(Request $request): Response
    {
        $this->authorize('view', SalesAgentConfig::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $user = $request->user();
        assert($user instanceof User);

        $config = SalesAgentConfig::forCompany($companyId);

        return Inertia::render('sales/agent/edit', [
            'tools' => SalesAgentConfig::toolsForFrontend(),
            'enabledTools' => $config?->enabled_tools ?? SalesAgentConfig::defaultEnabledTools(),
            'provider' => $config?->provider ?? SalesAgentConfig::defaultProvider(),
            'model' => $config?->model ?? '',
            'providers' => SalesAgentConfig::providersForFrontend(),
            'prompt' => filled($config?->prompt)
                ? $config->prompt
                : SalesAgentConfig::defaultPrompt(),
            'usesCustomPrompt' => filled($config?->prompt),
            'can' => [
                'update' => $user->can('update', SalesAgentConfig::class),
            ],
        ]);
    }

    public function update(
        UpdateAgentConfigRequest $request,
        UpdateAgentConfig $action,
    ): RedirectResponse {
        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $config = SalesAgentConfig::forCompany($companyId);
        $this->authorize('update', $config ?? SalesAgentConfig::class);

        $action->execute($companyId, $request->configPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Configuración del agente guardada.']);

        return to_route('sales.agent.edit');
    }
}
