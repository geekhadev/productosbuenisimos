<?php

namespace Database\Factories\Stock;

use App\Models\Company;
use App\Models\Stock\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $n = fake()->unique()->numberBetween(1, 999999);

        return [
            'company_id' => Company::factory(),
            'name' => 'Producto '.$n,
            'code' => 'CODE-'.$n,
            'sku' => 'SKU-'.$n,
            'width' => 0,
            'length' => 0,
            'height' => 0,
            'volume' => 0,
            'weight' => 0,
            'minimum_stock' => 0,
            'price' => 0,
            'is_active' => true,
        ];
    }
}
