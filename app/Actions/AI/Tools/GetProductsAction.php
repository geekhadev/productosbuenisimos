<?php

namespace App\Actions\AI\Tools;

use App\Models\Stock\Product;
use App\Support\Stock\ProductMediaPayload;

class GetProductsAction
{
    /**
     * @return list<array<string, mixed>>
     */
    public function execute(string $companyId): array
    {
        return Product::forCompany($companyId)
            ->where('is_active', true)
            ->with(['media' => function ($relation): void {
                $relation
                    ->orderBy('sort_order')
                    ->orderBy('created_at');
            }])
            ->get([
                'id', 'name', 'code', 'sku', 'price',
                'description',
                'weight', 'width', 'length', 'height', 'volume',
            ])
            ->map(function (Product $product): array {
                return [
                    ...$product->only([
                        'id', 'name', 'code', 'sku', 'price',
                        'description',
                        'weight', 'width', 'length', 'height', 'volume',
                    ]),
                    ...ProductMediaPayload::fromMedia($product->media),
                ];
            })
            ->values()
            ->all();
    }
}
