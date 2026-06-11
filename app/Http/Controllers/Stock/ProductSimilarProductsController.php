<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Product;
use App\Support\SelectedCompanySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSimilarProductsController extends Controller
{
    public function search(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $search = $request->string('q')->toString();

        $alreadySimilarIds = $product->similarProducts()->pluck('similar_product_id')->push($product->id);

        $results = Product::query()
            ->forCompany($companyId)
            ->whereNotIn('id', $alreadySimilarIds)
            ->searchFields($search !== '' ? $search : null)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'code', 'sku']);

        return response()->json([
            'results' => $results->map(fn (Product $p): array => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'sku' => $p->sku,
            ])->values(),
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'similar_product_id' => ['required', 'uuid'],
        ]);

        $similarProductId = $request->string('similar_product_id')->toString();

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $similarProduct = Product::query()
            ->forCompany($companyId)
            ->where('id', $similarProductId)
            ->firstOrFail();

        if ($similarProduct->id === $product->id) {
            return response()->json(['message' => 'Un producto no puede ser similar a sí mismo.'], 422);
        }

        $product->similarProducts()->syncWithoutDetaching([$similarProduct->id]);

        return response()->json($this->similarList($product));
    }

    public function destroy(Product $product, Product $similarProduct): JsonResponse
    {
        $this->authorize('update', $product);

        $product->similarProducts()->detach($similarProduct->id);

        return response()->json($this->similarList($product));
    }

    /**
     * @return array{similar: list<array<string, string>>}
     */
    private function similarList(Product $product): array
    {
        $items = $product->similarProducts()
            ->orderBy('name')
            ->get(['stock_products.id', 'name', 'code', 'sku']);

        return [
            'similar' => $items->map(fn (Product $p): array => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'sku' => $p->sku,
            ])->values()->all(),
        ];
    }
}
