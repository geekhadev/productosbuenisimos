<?php

namespace App\Actions\Landing;

use App\Models\Stock\Product;
use App\Support\LandingPublicCatalogCompany;

class ShowPublicProductAction
{
    /**
     * @return array<string, mixed>|null
     */
    public function execute(string $productId): ?array
    {
        $company = LandingPublicCatalogCompany::find();

        if ($company === null) {
            return null;
        }

        $product = Product::query()
            ->where('company_id', $company->id)
            ->where('id', $productId)
            ->where('is_active', true)
            ->first();

        if ($product === null) {
            return null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sku' => $product->sku,
            'description' => $product->description,
            'width' => $product->width,
            'length' => $product->length,
            'height' => $product->height,
            'volume' => $product->volume,
            'weight' => $product->weight,
            'minimum_stock' => $product->minimum_stock,
            'price' => $product->price,
        ];
    }
}
