<?php

namespace App\Services\Fulfillment\ContraEntrega\Data;

final readonly class ContraEntregaProductData
{
    public function __construct(
        public string $productSku,
        public int $quantity,
        public string $productName,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'productSku' => $this->productSku,
            'quantity' => $this->quantity,
            'productName' => $this->productName,
        ];
    }
}
