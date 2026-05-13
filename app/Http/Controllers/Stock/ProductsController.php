<?php

namespace App\Http\Controllers\Stock;

use App\Actions\Stock\Products\CreateProductAction;
use App\Actions\Stock\Products\DeactivateProductAction;
use App\Actions\Stock\Products\DeleteProductAction;
use App\Actions\Stock\Products\ListProductsAction;
use App\Actions\Stock\Products\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\ProductListRequest;
use App\Http\Requests\Stock\StoreProductRequest;
use App\Http\Requests\Stock\UpdateProductRequest;
use App\Models\Stock\Product;
use App\Models\User;
use App\Support\SelectedCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductsController extends Controller
{
    public function index(
        ProductListRequest $request,
        ListProductsAction $action,
    ): Response {
        $this->authorize('viewAny', Product::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $user = $request->user();
        assert($user instanceof User);

        $paginator = $action->execute($companyId, $request->filtersForAction());

        return Inertia::render('stock/products/index', [
            'data' => $paginator->through(
                fn (Product $product): array => $this->productRow($user, $product),
            ),
            'filters' => $request->filtersForFrontend(),
            'can' => [
                'create' => $user->can('create', Product::class),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Product::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        return Inertia::render('stock/products/form', [
            'product' => null,
        ]);
    }

    public function store(
        StoreProductRequest $request,
        CreateProductAction $action,
    ): RedirectResponse {
        $this->authorize('create', Product::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $product = $action->execute($companyId, $request->productPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto creado.']);

        return to_route('stock.products.edit', $product);
    }

    public function edit(Request $request, Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('stock/products/form', [
            'product' => $this->productFormProps($product),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProductAction $action,
    ): RedirectResponse {
        $this->authorize('update', $product);

        $action->execute($product, $request->productPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto actualizado.']);

        return to_route('stock.products.edit', $product);
    }

    public function deactivate(Request $request, Product $product, DeactivateProductAction $action): RedirectResponse
    {
        $this->authorize('deactivate', $product);

        if (! $product->is_active) {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'El producto ya estaba inactivo.']);

            return back();
        }

        $action->execute($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto desactivado.']);

        return back();
    }

    public function destroy(Request $request, Product $product, DeleteProductAction $action): RedirectResponse
    {
        $this->authorize('delete', $product);

        $action->execute($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto eliminado.']);

        return to_route('stock.products.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function productFormProps(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sku' => $product->sku,
            'width' => (string) $product->width,
            'length' => (string) $product->length,
            'height' => (string) $product->height,
            'volume' => (string) $product->volume,
            'weight' => (string) $product->weight,
            'minimum_stock' => $product->minimum_stock,
            'price' => (string) $product->price,
            'is_active' => $product->is_active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productRow(User $user, Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sku' => $product->sku,
            'width' => (string) $product->width,
            'length' => (string) $product->length,
            'height' => (string) $product->height,
            'volume' => (string) $product->volume,
            'weight' => (string) $product->weight,
            'minimum_stock' => $product->minimum_stock,
            'price' => (string) $product->price,
            'is_active' => $product->is_active,
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
            'can' => [
                'update' => $user->can('update', $product),
                'delete' => $user->can('delete', $product),
                'deactivate' => $user->can('deactivate', $product),
            ],
        ];
    }
}
