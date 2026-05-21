<?php

namespace App\Actions\Sales\Leads;

use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Sales\Lead;

class CreateLeadAction
{
    /**
     * @param  array{phone: string, source: string}  $attributes
     */
    public function execute(string $companyId, array $attributes): Lead
    {
        return Lead::query()->create([
            'company_id' => $companyId,
            'phone' => $attributes['phone'],
            'source' => LeadSource::from($attributes['source']),
            'status' => LeadStatus::Nuevo,
        ]);
    }
}
