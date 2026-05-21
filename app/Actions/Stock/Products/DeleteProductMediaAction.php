<?php

namespace App\Actions\Stock\Products;

use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Support\Stock\ProductMediaPayload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteProductMediaAction
{
    /**
     * @return array{images: list<array<string, mixed>>, video: array<string, mixed>|null}
     */
    public function execute(Product $product, ProductMedia $media): array
    {
        if (! Storage::disk($media->disk)->exists($media->path)) {
            Log::warning('Product media file missing on disk during delete.', [
                'media_id' => $media->id,
                'path' => $media->path,
                'disk' => $media->disk,
            ]);
        } else {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        $product->load('media');

        return ProductMediaPayload::forProduct($product);
    }
}
