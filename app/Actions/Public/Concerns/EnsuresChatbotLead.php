<?php

namespace App\Actions\Public\Concerns;

use App\Actions\Sales\Leads\EnsureLeadForPhoneAction;
use App\Enums\ChatbotSource;

trait EnsuresChatbotLead
{
    protected function ensureLeadForPhone(string $companyId, string $phone, ChatbotSource $source): void
    {
        (new EnsureLeadForPhoneAction)->execute($companyId, $source->value, $phone);
    }
}
