<?php

namespace App\Services\Fulfillment\ContraEntrega\Data;

final readonly class ContraEntregaCustomerData
{
    public function __construct(
        public string $names,
        public int $phone,
        public string $phonePrefix,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'names' => $this->names,
            'phone' => $this->phone,
            'phonePrefix' => $this->phonePrefix,
        ];
    }
}
