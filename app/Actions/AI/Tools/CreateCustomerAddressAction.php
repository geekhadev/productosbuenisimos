<?php

namespace App\Actions\AI\Tools;

use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;

class CreateCustomerAddressAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(
        string $companyId,
        string $customerId,
        ?string $countryName,
        ?string $stateName,
        ?string $address,
    ): array {
        $customer = Customer::forCompany($companyId)
            ->find($customerId, ['id']);

        if ($customer === null) {
            return ['error' => 'No se encontró el cliente en la empresa.'];
        }

        $countryName = $this->normalize($countryName);
        $stateName = $this->normalize($stateName);
        $address = $this->normalize($address);

        if ($countryName === null && $stateName === null && $address === null) {
            return ['error' => 'Debes proporcionar al menos uno de: country_name, state_name o address.'];
        }

        $nextSortOrder = (int) CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->max('sort_order') + 1;

        $customerAddress = CustomerAddress::query()->create([
            'customer_id' => $customerId,
            'sort_order' => $nextSortOrder,
            'country_name' => $countryName,
            'state_name' => $stateName,
            'address' => $address,
        ]);

        return $customerAddress->only([
            'id',
            'customer_id',
            'sort_order',
            'country_name',
            'state_name',
            'address',
        ]);
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
