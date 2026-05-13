<?php

namespace App\Actions\Stock\Products;

use App\Models\Stock\Product;

class CreateProductAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(string $companyId, array $attributes): Product
    {
        return Product::query()->create([
            'company_id' => $companyId,
            'name' => $attributes['name'],
            'code' => $attributes['code'],
            'sku' => $attributes['sku'],
            'width' => $attributes['width'] ?? 0,
            'length' => $attributes['length'] ?? 0,
            'height' => $attributes['height'] ?? 0,
            'volume' => $attributes['volume'] ?? 0,
            'weight' => $attributes['weight'] ?? 0,
            'minimum_stock' => $attributes['minimum_stock'] ?? 0,
            'price' => $attributes['price'] ?? 0,
            'is_active' => $attributes['is_active'] ?? true,
        ]);
    }
}
