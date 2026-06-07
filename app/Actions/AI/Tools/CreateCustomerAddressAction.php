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
        ?string $cityName = null,
        ?string $districtName = null,
        ?string $streetPrefix = null,
        ?string $houseNumber = null,
        ?string $zipCode = null,
        ?string $reference = null,
        ?string $address = null,
    ): array {
        $customer = Customer::forCompany($companyId)
            ->find($customerId, ['id']);

        if ($customer === null) {
            return ['error' => 'No se encontró el cliente en la empresa.'];
        }

        $countryName = $this->normalize($countryName);
        $stateName = $this->normalize($stateName);
        $cityName = $this->normalize($cityName);
        $districtName = $this->normalize($districtName);
        $streetPrefix = $this->normalize($streetPrefix);
        $houseNumber = $this->normalize($houseNumber);
        $zipCode = $this->normalize($zipCode);
        $reference = $this->normalize($reference);
        $address = $this->normalize($address);

        $hasData = $countryName !== null
            || $stateName !== null
            || $cityName !== null
            || $districtName !== null
            || $address !== null;

        if (! $hasData) {
            return ['error' => 'Debes proporcionar al menos uno de: country_name, state_name, city_name, district_name o address.'];
        }

        $nextSortOrder = (int) CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->max('sort_order') + 1;

        $customerAddress = CustomerAddress::query()->create([
            'customer_id' => $customerId,
            'sort_order' => $nextSortOrder,
            'country_name' => $countryName,
            'state_name' => $stateName,
            'city_name' => $cityName,
            'district_name' => $districtName,
            'street_prefix' => $streetPrefix,
            'house_number' => $houseNumber,
            'zip_code' => $zipCode,
            'reference' => $reference,
            'address' => $address,
        ]);

        return $customerAddress->only([
            'id',
            'customer_id',
            'sort_order',
            'country_name',
            'state_name',
            'city_name',
            'district_name',
            'street_prefix',
            'house_number',
            'zip_code',
            'reference',
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
