<?php

namespace App\Actions\Landing;

use App\Models\Stock\Product;
use App\Support\Landing\PublicLandingProductSerializer;
use App\Support\LandingPublicCatalogCompany;

class GetPublicProductsAction
{
    /**
     * Returns the public product list for the fixed company.
     * Company is resolved server-side; never accept company_id from user input.
     *
     * @return array{name: string, heroProduct: array<string,mixed>|null, showcaseProducts: list<array<string,mixed>>}
     */
    public function execute(): array
    {
        $company = LandingPublicCatalogCompany::find();

        if ($company === null) {
            return [
                'name' => LandingPublicCatalogCompany::NAME,
                'heroProduct' => null,
                'showcaseProducts' => [],
            ];
        }

        $products = Product::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->withLandingImages()
            ->orderBy('created_at', 'asc')
            ->get(['id', 'name', 'code', 'sku', 'price', 'description']);

        $serialized = $products->map(
            fn (Product $product): array => PublicLandingProductSerializer::forList($product),
        );

        return [
            'name' => $company->name,
            'heroProduct' => $serialized->first(),
            'showcaseProducts' => $serialized->skip(1)->values()->all(),
        ];
    }
}
