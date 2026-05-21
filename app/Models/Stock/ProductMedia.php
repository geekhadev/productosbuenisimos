<?php

namespace App\Models\Stock;

use App\Enums\Stock\ProductMediaType;
use Database\Factories\Stock\ProductMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'type',
    'path',
    'sort_order',
    'disk',
    'mime_type',
    'size',
    'original_name',
])]
class ProductMedia extends Model
{
    /** @use HasFactory<ProductMediaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'stock_product_media';

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function resolveRouteBinding($value, $field = null): static
    {
        /** @var Product $product */
        $product = request()->route('product');

        /** @var self */
        return static::query()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->where('product_id', $product->id)
            ->where('type', ProductMediaType::Image->value)
            ->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProductMediaType::class,
            'sort_order' => 'integer',
            'size' => 'integer',
        ];
    }

    protected static function newFactory(): ProductMediaFactory
    {
        return ProductMediaFactory::new();
    }
}
