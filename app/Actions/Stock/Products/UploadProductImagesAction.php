<?php

namespace App\Actions\Stock\Products;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Support\Media\ImageOptimizer;
use App\Support\Media\ImageResizer;
use App\Support\Media\MediaUploader;
use App\Support\Stock\ProductMediaPayload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class UploadProductImagesAction
{
    /**
     * @param  list<UploadedFile>  $files
     * @return array{images: list<array<string, mixed>>, video: array<string, mixed>|null}
     */
    public function execute(Product $product, array $files): array
    {
        $disk = (string) config('media.disk', 'public');
        $maxBytes = (int) config('media.image.max_bytes');
        $maxSide = (int) config('media.image.max_side_px');
        $uploader = new MediaUploader;

        $nextSort = (int) $product->media()
            ->where('type', ProductMediaType::Image)
            ->max('sort_order');

        $created = new Collection;

        foreach ($files as $file) {
            $nextSort++;

            $tempPath = ImageOptimizer::optimize($file, $maxBytes);
            $tempPath = ImageResizer::resize($tempPath, $maxSide);
            $tempPath = ImageResizer::toWebp($tempPath);

            $storedPath = $uploader->upload(
                $tempPath,
                'products/'.$product->id.'/images',
                $disk,
            );

            $size = (int) Storage::disk($disk)->size($storedPath);

            $media = ProductMedia::query()->create([
                'product_id' => $product->id,
                'type' => ProductMediaType::Image,
                'path' => $storedPath,
                'sort_order' => $nextSort,
                'disk' => $disk,
                'mime_type' => 'image/webp',
                'size' => $size,
                'original_name' => $file->getClientOriginalName(),
            ]);

            $created->push($media);
        }

        $product->load('media');

        return ProductMediaPayload::forProduct($product);
    }
}
