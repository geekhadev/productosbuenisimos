<?php

namespace App\Actions\Sales\AgentConfig;

use App\Models\Sales\SalesAgentConfig;

class UpdateAgentConfig
{
    /**
     * @param  list<string>  $enabledTools
     */
    public function execute(string $companyId, array $enabledTools, ?string $prompt): SalesAgentConfig
    {
        $config = SalesAgentConfig::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'enabled_tools' => $enabledTools,
                'prompt' => $prompt,
            ],
        );

        SalesAgentConfig::forgetCacheForCompany($companyId);

        return $config;
    }
}
