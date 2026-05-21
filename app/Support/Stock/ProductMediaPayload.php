<?php

namespace App\Support\Stock;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use Illuminate\Support\Facades\Storage;

class ProductMediaPayload
{
    /**
     * @return array{images: list<array<string, mixed>>, video: array<string, mixed>|null}
     */
    public static function forProduct(Product $product): array
    {
        $media = $product->media()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $images = $media
            ->filter(fn (ProductMedia $item): bool => $item->type === ProductMediaType::Image)
            ->values()
            ->map(fn (ProductMedia $item): array => self::item($item))
            ->all();

        $video = $media
            ->first(fn (ProductMedia $item): bool => $item->type === ProductMediaType::Video);

        return [
            'images' => $images,
            'video' => $video !== null ? self::item($video) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function item(ProductMedia $media): array
    {
        return [
            'id' => $media->id,
            'url' => self::publicUrl($media),
            'sort_order' => $media->sort_order,
            'mime_type' => $media->mime_type,
            'original_name' => $media->original_name,
            'size' => $media->size,
        ];
    }

    /**
     * Ruta pública relativa al host actual (evita desajustes con APP_URL y funciona tras storage:link).
     */
    public static function publicUrl(ProductMedia $media): string
    {
        if ($media->disk === 'public') {
            return '/storage/'.ltrim($media->path, '/');
        }

        return Storage::disk($media->disk)->url($media->path);
    }
}
