<?php

namespace App\Actions\AI\Tools;

use App\Models\Stock\Product;
use App\Support\Stock\ProductMediaPayload;
use Illuminate\Support\Facades\Validator;

class GetSimilarProductsAction
{
    /**
     * @param  array<string, mixed>  $parameters
     * @return list<array<string, mixed>>|array{error: mixed}
     */
    public function execute(string $companyId, array $parameters): array
    {
        $validator = Validator::make($parameters, [
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        $productIds = $validator->validated()['product_ids'];

        // Load the ordered products with their similar products (active only, same company)
        $orderedProducts = Product::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $productIds)
            ->with(['similarProducts' => function ($relation) use ($companyId): void {
                $relation
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->with(['media' => function ($media): void {
                        $media->orderBy('sort_order')->orderBy('created_at');
                    }]);
            }])
            ->get();

        // Collect unique similar products, excluding products already in the order
        $seen = array_flip($productIds);
        $similar = [];

        foreach ($orderedProducts as $product) {
            foreach ($product->similarProducts as $sp) {
                if (isset($seen[$sp->id])) {
                    continue;
                }
                $seen[$sp->id] = true;
                $similar[] = [
                    ...$sp->only(['id', 'name', 'code', 'sku', 'price', 'description']),
                    ...ProductMediaPayload::fromMedia($sp->media),
                ];
            }
        }

        return array_values($similar);
    }
}
