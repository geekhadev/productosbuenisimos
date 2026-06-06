<?php

namespace App\Services\Fulfillment\ContraEntrega\Data;

use Carbon\CarbonInterface;

final readonly class ContraEntregaOrderData
{
    /**
     * @param  list<ContraEntregaProductData>  $products
     */
    public function __construct(
        public string $orderNumber,
        public ContraEntregaCustomerData $customer,
        public ContraEntregaAddressData $deliveryAddress,
        public array $products,
        public CarbonInterface $deliveryDate,
        public float $totalPrice,
        public string $currency,
        public string $serviceType,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'orderNumber' => $this->orderNumber,
            'customer' => $this->customer->toArray(),
            'deliveryAddress' => $this->deliveryAddress->toArray(),
            'products' => array_map(fn (ContraEntregaProductData $p) => $p->toArray(), $this->products),
            'deliveryDate' => $this->deliveryDate->format('Y-m-d'),
            'totalPrice' => $this->totalPrice,
            'currency' => $this->currency,
            'serviceType' => $this->serviceType,
        ];
    }
}
