<?php

namespace App\Actions\Landing;

use App\Models\Stock\Product;
use App\Support\Landing\PublicLandingProductSerializer;
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
            ->withLandingDetailMedia()
            ->first();

        if ($product === null) {
            return null;
        }

        return PublicLandingProductSerializer::forDetail($product);
    }
}
