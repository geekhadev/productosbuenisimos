<?php

use App\Actions\AI\Tools\GetProductsAction;
use App\Ai\Tools\GetProducts;
use App\Enums\Stock\ProductMediaType;
use App\Models\Company;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request;

test('get products action returns only active products for the company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    $active = Product::factory()->for($company)->create([
        'name' => 'Activo',
        'code' => 'ACT-1',
        'sku' => 'SKU-ACT-1',
        'price' => 150,
        'description' => 'Descripción activa',
        'weight' => 0.05,
        'width' => 2,
        'length' => 2,
        'height' => 0.5,
        'volume' => 2,
        'is_active' => true,
    ]);

    Product::factory()->for($company)->create(['is_active' => false]);
    Product::factory()->for($otherCompany)->create(['is_active' => true]);

    $deleted = Product::factory()->for($company)->create(['is_active' => true]);
    $deleted->delete();

    $products = (new GetProductsAction)->execute($company->id);

    expect($products)->toHaveCount(1)
        ->and($products[0]['id'])->toBe($active->id)
        ->and($products[0]['name'])->toBe('Activo')
        ->and($products[0]['code'])->toBe('ACT-1')
        ->and($products[0]['sku'])->toBe('SKU-ACT-1')
        ->and($products[0]['price'])->toBe('150.00')
        ->and($products[0]['description'])->toBe('Descripción activa')
        ->and($products[0]['weight'])->toBe('0.050')
        ->and($products[0]['width'])->toBe('2.000')
        ->and($products[0]['length'])->toBe('2.000')
        ->and($products[0]['height'])->toBe('0.500')
        ->and($products[0]['volume'])->toBe('2.000');
});

test('get products action returns empty list when company has no active products', function () {
    $company = Company::factory()->create();

    Product::factory()->for($company)->create(['is_active' => false]);

    expect((new GetProductsAction)->execute($company->id))->toBe([]);
});

test('get products tool returns json catalog for the injected company', function () {
    $company = Company::factory()->create();

    Product::factory()->for($company)->create([
        'name' => 'Producto tool',
        'code' => 'TOOL-1',
        'sku' => 'SKU-TOOL-1',
        'price' => 99.5,
        'is_active' => true,
    ]);

    $response = (new GetProducts($company->id))->handle(new Request);

    $decoded = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toHaveCount(1)
        ->and($decoded[0]['name'])->toBe('Producto tool')
        ->and($decoded[0]['price'])->toBe('99.50');
});

test('get products tool exposes expected name and empty schema', function () {
    $tool = new GetProducts((string) Str::uuid());

    expect($tool->name())->toBe('get_products')
        ->and($tool->schema(new JsonSchemaTypeFactory))->toBe([]);
});

test('get products action returns image and video urls', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->for($company)->create(['is_active' => true]);

    $firstImage = ProductMedia::factory()->for($product)->create([
        'path' => 'products/'.$product->id.'/images/a.webp',
        'sort_order' => 0,
    ]);
    $secondImage = ProductMedia::factory()->for($product)->create([
        'path' => 'products/'.$product->id.'/images/b.webp',
        'sort_order' => 1,
    ]);
    $video = ProductMedia::factory()->for($product)->create([
        'type' => ProductMediaType::Video,
        'path' => 'products/'.$product->id.'/videos/clip.mp4',
        'mime_type' => 'video/mp4',
        'original_name' => 'clip.mp4',
    ]);

    $products = (new GetProductsAction)->execute($company->id);

    expect($products)->toHaveCount(1)
        ->and($products[0]['images'])->toHaveCount(2)
        ->and($products[0]['images'][0]['url'])->toBe('/storage/'.$firstImage->path)
        ->and($products[0]['images'][1]['url'])->toBe('/storage/'.$secondImage->path)
        ->and($products[0]['video']['url'])->toBe('/storage/'.$video->path);
});

test('get products action returns empty media when product has no images or video', function () {
    $company = Company::factory()->create();
    Product::factory()->for($company)->create(['is_active' => true]);

    $products = (new GetProductsAction)->execute($company->id);

    expect($products[0]['images'])->toBe([])
        ->and($products[0]['video'])->toBeNull();
});
