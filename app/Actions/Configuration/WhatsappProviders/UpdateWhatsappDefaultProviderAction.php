<?php

namespace App\Actions\Configuration\WhatsappProviders;

use App\Models\Configuration\WhatsappSetting;
use App\Support\WhatsappConfigurationBridge;

class UpdateWhatsappDefaultProviderAction
{
    public function execute(string $provider): WhatsappSetting
    {
        $settings = WhatsappSetting::instance();
        $settings->update(['default_provider' => $provider]);

        WhatsappConfigurationBridge::apply();

        return $settings->refresh();
    }
}
