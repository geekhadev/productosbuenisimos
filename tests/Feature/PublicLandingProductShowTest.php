<?php

use App\Models\Company;
use App\Models\Stock\Product;
use App\Support\LandingPublicCatalogCompany;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;

test('public product detail renders for active catalog product', function () {
    $company = Company::factory()->create(['name' => LandingPublicCatalogCompany::NAME]);
    $product = Product::factory()->for($company)->create([
        'name' => 'Imán de prueba',
        'code' => 'CODE-1',
        'sku' => 'SKU-1',
        'width' => 1.5,
        'length' => 2,
        'height' => 0.25,
        'volume' => 0.75,
        'weight' => 0.1,
        'minimum_stock' => 10,
        'price' => 3990,
        'is_active' => true,
        'description' => 'Descripción de prueba.',
    ]);

    $this->get(route('landing.products.show', $product->id))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('landing/product-show')
            ->where('canRegister', Features::enabled(Features::registration()))
            ->has('product')
            ->where('product.name', 'Imán de prueba')
            ->where('product.code', 'CODE-1')
            ->where('product.sku', 'SKU-1')
            ->where('product.minimum_stock', 10)
            ->where('product.description', 'Descripción de prueba.')
        );
});

test('public product detail returns 404 for inactive product', function () {
    $company = Company::factory()->create(['name' => LandingPublicCatalogCompany::NAME]);
    $product = Product::factory()->for($company)->create(['is_active' => false]);

    $this->get(route('landing.products.show', $product->id))
        ->assertNotFound();
});

test('public product detail returns 404 for product of another company', function () {
    Company::factory()->create(['name' => LandingPublicCatalogCompany::NAME]);
    $otherCompany = Company::factory()->create();
    $product = Product::factory()->for($otherCompany)->create(['is_active' => true]);

    $this->get(route('landing.products.show', $product->id))
        ->assertNotFound();
});

test('public product detail returns 404 for unknown uuid', function () {
    $this->get(route('landing.products.show', (string) Str::uuid()))
        ->assertNotFound();
});

test('public product detail returns 404 for soft deleted product', function () {
    $company = Company::factory()->create(['name' => LandingPublicCatalogCompany::NAME]);
    $product = Product::factory()->for($company)->create(['is_active' => true]);
    $product->delete();

    $this->get(route('landing.products.show', $product->id))
        ->assertNotFound();
});
