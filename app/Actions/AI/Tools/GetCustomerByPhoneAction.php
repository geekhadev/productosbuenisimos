<?php

namespace App\Actions\AI\Tools;

use App\Models\Sales\Customer;

class GetCustomerByPhoneAction
{
    /**
     * @return array<string, mixed>|null
     */
    public function execute(string $companyId, string $phone): ?array
    {
        $customer = Customer::forCompany($companyId)
            ->where('phone', $phone)
            ->first(['id', 'full_name', 'phone']);

        if ($customer === null) {
            return null;
        }

        return [
            'id' => $customer->id,
            'full_name' => $customer->full_name,
            'phone' => $customer->phone,
        ];
    }
}
