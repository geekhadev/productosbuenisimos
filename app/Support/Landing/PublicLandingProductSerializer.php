<?php

namespace App\Support\Landing;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Support\Stock\ProductMediaPayload;

class PublicLandingProductSerializer
{
    public const PLACEHOLDER_URL = '/product-placeholder.svg';

    /**
     * @return list<array{id: string, url: string, alt: string}>
     */
    public static function images(Product $product): array
    {
        if (! $product->relationLoaded('media')) {
            $product->load(['media' => function ($relation): void {
                $relation
                    ->where('type', ProductMediaType::Image->value)
                    ->orderBy('sort_order')
                    ->orderBy('created_at');
            }]);
        }

        return $product->media
            ->filter(fn (ProductMedia $item): bool => $item->type === ProductMediaType::Image)
            ->values()
            ->map(fn (ProductMedia $item): array => [
                'id' => $item->id,
                'url' => ProductMediaPayload::publicUrl($item),
                'alt' => $item->original_name,
            ])
            ->all();
    }

    public static function thumbnailUrl(Product $product): string
    {
        $images = self::images($product);

        return $images[0]['url'] ?? self::PLACEHOLDER_URL;
    }

    /**
     * @return array<string, mixed>
     */
    public static function forList(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sku' => $product->sku,
            'price' => $product->price,
            'description' => $product->description,
            'thumbnail_url' => self::thumbnailUrl($product),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forDetail(Product $product): array
    {
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
            'thumbnail_url' => self::thumbnailUrl($product),
            'images' => self::images($product),
        ];
    }
}
