<?php

namespace App\Actions\Sales\AgentConfig;

use App\Models\Sales\SalesAgentConfig;

class UpdateAgentConfig
{
    /**
     * @param  array{
     *     enabled_tools: list<string>,
     *     provider: string,
     *     model: string,
     *     prompt: ?string,
     * }  $attributes
     */
    public function execute(string $companyId, array $attributes): SalesAgentConfig
    {
        $config = SalesAgentConfig::query()->updateOrCreate(
            ['company_id' => $companyId],
            $attributes,
        );

        SalesAgentConfig::forgetCacheForCompany($companyId);

        return $config;
    }
}
