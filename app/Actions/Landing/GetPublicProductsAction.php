<?php

namespace App\Actions\Landing;

use App\Models\Company;
use App\Models\Stock\Product;

class GetPublicProductsAction
{
    private const COMPANY_NAME = 'PRODUCTOS BUENISIMOS SPA';

    /**
     * Returns the public product list for the fixed company.
     * Company is resolved server-side; never accept company_id from user input.
     *
     * @return array{name: string, heroProduct: array<string,mixed>|null, showcaseProducts: list<array<string,mixed>>}
     */
    public function execute(): array
    {
        $company = Company::query()
            ->where('name', self::COMPANY_NAME)
            ->first();

        if ($company === null) {
            return [
                'name' => self::COMPANY_NAME,
                'heroProduct' => null,
                'showcaseProducts' => [],
            ];
        }

        $products = Product::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'name', 'code', 'sku', 'price', 'description']);

        return [
            'name' => $company->name,
            'heroProduct' => $products->first()?->toArray(),
            'showcaseProducts' => $products->skip(1)->values()->toArray(),
        ];
    }
}
