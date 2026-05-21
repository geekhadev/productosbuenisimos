<?php

namespace App\Actions\Stock\Products;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Support\Stock\ProductMediaPayload;

class ReorderProductImagesAction
{
    /**
     * @param  list<string>  $orderedIds
     * @return array{images: list<array<string, mixed>>, video: array<string, mixed>|null}
     */
    public function execute(Product $product, array $orderedIds): array
    {
        foreach ($orderedIds as $index => $mediaId) {
            $product->media()
                ->where('type', ProductMediaType::Image)
                ->whereKey($mediaId)
                ->update(['sort_order' => $index]);
        }

        $product->load('media');

        return ProductMediaPayload::forProduct($product);
    }
}
