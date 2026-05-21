<?php

namespace App\Actions\Sales\Leads;

use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;
use Illuminate\Support\Facades\Validator;

class EnsureLeadForPhoneAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(string $companyId, string $source, string $phone): array
    {
        $validator = Validator::make(['phone' => $phone], [
            'phone' => ['required', 'string', 'max:40'],
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        $existing = Lead::forCompany($companyId)
            ->where('phone', $phone)
            ->first();

        if ($existing !== null) {
            return $existing->load('customer:id,full_name,phone')->toArray();
        }

        $customer = Customer::forCompany($companyId)
            ->where('phone', $phone)
            ->first(['id', 'full_name', 'phone']);

        $lead = Lead::query()->create([
            'company_id' => $companyId,
            'phone' => $phone,
            'source' => LeadSource::from($source),
            'status' => $customer !== null ? LeadStatus::Convertido : LeadStatus::Nuevo,
            'customer_id' => $customer?->id,
        ]);

        return $lead->load('customer:id,full_name,phone')->toArray();
    }
}
