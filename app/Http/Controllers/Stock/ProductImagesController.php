<?php

namespace App\Http\Controllers\Stock;

use App\Actions\Stock\Products\DeleteProductMediaAction;
use App\Actions\Stock\Products\ReorderProductImagesAction;
use App\Actions\Stock\Products\UploadProductImagesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\ReorderProductImagesRequest;
use App\Http\Requests\Stock\UploadProductImagesRequest;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use Illuminate\Http\JsonResponse;

class ProductImagesController extends Controller
{
    public function store(
        UploadProductImagesRequest $request,
        Product $product,
        UploadProductImagesAction $action,
    ): JsonResponse {
        $this->authorize('update', $product);

        return response()->json(
            $action->execute($product, $request->uploadedImages()),
        );
    }

    public function reorder(
        ReorderProductImagesRequest $request,
        Product $product,
        ReorderProductImagesAction $action,
    ): JsonResponse {
        $this->authorize('update', $product);

        return response()->json(
            $action->execute($product, $request->orderedIds()),
        );
    }

    public function destroy(
        Product $product,
        ProductMedia $media,
        DeleteProductMediaAction $action,
    ): JsonResponse {
        $this->authorize('update', $product);

        return response()->json(
            $action->execute($product, $media),
        );
    }
}
