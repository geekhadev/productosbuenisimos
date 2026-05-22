<?php

namespace App\Actions\Sales\Leads;

use App\Enums\Sales\LeadStatus;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;

class AssociateLeadsWithCustomerForPhoneAction
{
    /**
     * Vincula leads activos sin cliente de la empresa que coinciden en teléfono.
     */
    public function execute(string $companyId, string $phone, Customer $customer): int
    {
        return Lead::forCompany($companyId)
            ->where('phone', $phone)
            ->whereNull('customer_id')
            ->update([
                'customer_id' => $customer->id,
                'status' => LeadStatus::Convertido,
            ]);
    }
}
