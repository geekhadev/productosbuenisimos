<?php

namespace App\Actions\Sales\Leads;

use App\Models\Sales\Lead;

class DeleteLeadAction
{
    public function execute(Lead $lead): void
    {
        $lead->delete();
    }
}
