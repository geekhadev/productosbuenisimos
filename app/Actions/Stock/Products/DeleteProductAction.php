<?php

namespace App\Actions\Stock\Products;

use App\Models\Stock\Product;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        $product->delete();
    }
}
