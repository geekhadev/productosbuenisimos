<?php

namespace App\Actions\AI\Tools;

use App\Models\Stock\Product;

class GetProductsAction
{
    /**
     * @return list<array<string, mixed>>
     */
    public function execute(string $companyId): array
    {
        return Product::forCompany($companyId)
            ->where('is_active', true)
            ->get([
                'id', 'name', 'code', 'sku', 'price',
                'description',
                'weight', 'width', 'length', 'height', 'volume',
            ])
            ->toArray();
    }
}
