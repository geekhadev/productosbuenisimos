<?php

namespace Database\Factories\Stock;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductMedia>
 */
class ProductMediaFactory extends Factory
{
    protected $model = ProductMedia::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = (string) Str::uuid();

        return [
            'product_id' => Product::factory(),
            'type' => ProductMediaType::Image,
            'path' => 'products/'.$id.'/images/'.$id.'.webp',
            'sort_order' => 0,
            'disk' => 'public',
            'mime_type' => 'image/webp',
            'size' => 1024,
            'original_name' => 'photo.webp',
        ];
    }

    public function video(): static
    {
        return $this->state(function (array $attributes): array {
            $id = (string) Str::uuid();
            $productId = $attributes['product_id'] instanceof Product
                ? $attributes['product_id']->id
                : (string) $attributes['product_id'];

            return [
                'type' => ProductMediaType::Video,
                'path' => 'products/'.$productId.'/videos/'.$id.'.mp4',
                'sort_order' => 0,
                'mime_type' => 'video/mp4',
                'original_name' => 'clip.mp4',
            ];
        });
    }
}
