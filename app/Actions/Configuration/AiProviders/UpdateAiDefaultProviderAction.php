<?php

namespace App\Actions\Configuration\AiProviders;

use App\Models\Configuration\AiSetting;
use App\Support\AiConfigurationBridge;

class UpdateAiDefaultProviderAction
{
    public function execute(string $provider): AiSetting
    {
        $settings = AiSetting::instance();
        $settings->update(['default_provider' => $provider]);

        AiConfigurationBridge::apply();

        return $settings->refresh();
    }
}
