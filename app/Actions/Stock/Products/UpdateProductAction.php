<?php

namespace App\Actions\Stock\Products;

use App\Models\Stock\Product;

class UpdateProductAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Product $product, array $attributes): Product
    {
        $product->fill([
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
        $product->save();

        return $product->refresh();
    }
}
