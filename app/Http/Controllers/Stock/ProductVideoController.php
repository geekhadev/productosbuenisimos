<?php

namespace App\Http\Controllers\Stock;

use App\Actions\Stock\Products\DeleteProductMediaAction;
use App\Actions\Stock\Products\UploadProductVideoAction;
use App\Enums\Stock\ProductMediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\UploadProductVideoRequest;
use App\Models\Stock\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class ProductVideoController extends Controller
{
    public function store(
        UploadProductVideoRequest $request,
        Product $product,
        UploadProductVideoAction $action,
    ): JsonResponse {
        $this->authorize('update', $product);

        /** @var UploadedFile $video */
        $video = $request->file('video');

        return response()->json(
            $action->execute($product, $video),
        );
    }

    public function destroy(
        Product $product,
        DeleteProductMediaAction $action,
    ): JsonResponse {
        $this->authorize('update', $product);

        $media = $product->media()
            ->where('type', ProductMediaType::Video)
            ->firstOrFail();

        return response()->json(
            $action->execute($product, $media),
        );
    }
}
