<?php

namespace App\Services\Fulfillment\ContraEntrega\Data;

final readonly class ContraEntregaAddressData
{
    public function __construct(
        public string $country,
        public string $state,
        public string $city,
        public string $district,
        public string $streetPreffix,
        public string $streetName,
        public string $houseNumber,
        public string $zipCode,
        public string $reference,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'district' => $this->district,
            'streetPreffix' => $this->streetPreffix,
            'streetName' => $this->streetName,
            'houseNumber' => $this->houseNumber,
            'zipCode' => $this->zipCode,
            'reference' => $this->reference,
        ];
    }
}
