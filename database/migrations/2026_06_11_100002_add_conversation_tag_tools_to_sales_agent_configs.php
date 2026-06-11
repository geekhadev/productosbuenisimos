<?php

use App\Models\Sales\SalesAgentConfig;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const NEW_TOOLS = [
        SalesAgentConfig::TOOL_GET_CONVERSATION_TAGS,
        SalesAgentConfig::TOOL_UPDATE_CONVERSATION_TAG,
    ];

    public function up(): void
    {
        SalesAgentConfig::all()->each(function (SalesAgentConfig $config): void {
            $current = $config->enabled_tools ?? [];
            $missing = array_values(array_diff(self::NEW_TOOLS, $current));

            if ($missing === []) {
                return;
            }

            $config->update(['enabled_tools' => array_merge($current, $missing)]);
            SalesAgentConfig::forgetCacheForCompany($config->company_id);
        });
    }

    public function down(): void
    {
        SalesAgentConfig::all()->each(function (SalesAgentConfig $config): void {
            $filtered = array_values(array_diff($config->enabled_tools ?? [], self::NEW_TOOLS));
            $config->update(['enabled_tools' => $filtered]);
            SalesAgentConfig::forgetCacheForCompany($config->company_id);
        });
    }
};
