<?php

namespace App\Actions\Stock\Products;

use App\Models\Stock\Product;

class DeactivateProductAction
{
    public function execute(Product $product): Product
    {
        $product->is_active = false;
        $product->save();

        return $product->refresh();
    }
}
