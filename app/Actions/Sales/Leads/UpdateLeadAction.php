<?php

namespace App\Actions\Sales\Leads;

use App\Enums\Sales\LeadStatus;
use App\Models\Sales\Lead;

class UpdateLeadAction
{
    /**
     * @param  array{status: string, customer_id: ?string}  $attributes
     */
    public function execute(Lead $lead, array $attributes): Lead
    {
        $lead->fill([
            'status' => LeadStatus::from($attributes['status']),
            'customer_id' => $attributes['customer_id'],
        ]);
        $lead->save();
        $lead->load('customer');

        return $lead;
    }
}
