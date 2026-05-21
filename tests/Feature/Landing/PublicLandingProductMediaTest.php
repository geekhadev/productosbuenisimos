<?php

use App\Models\Company;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Support\LandingPublicCatalogCompany;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('landing index exposes first product image as thumbnail', function () {
    $company = Company::factory()->create(['name' => LandingPublicCatalogCompany::NAME]);
    $product = Product::factory()->for($company)->create(['is_active' => true]);
    $media = ProductMedia::factory()->for($product)->create([
        'path' => 'products/'.$product->id.'/images/hero.webp',
        'sort_order' => 0,
    ]);
    Storage::disk('public')->put($media->path, 'image-bytes');

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('heroProduct.thumbnail_url', '/storage/'.$media->path)
            ->where('heroProduct.id', $product->id));
});

test('product detail exposes ordered images for gallery', function () {
    $company = Company::factory()->create(['name' => LandingPublicCatalogCompany::NAME]);
    $product = Product::factory()->for($company)->create(['is_active' => true, 'name' => 'Galería test']);

    $first = ProductMedia::factory()->for($product)->create([
        'path' => 'products/'.$product->id.'/images/a.webp',
        'sort_order' => 0,
        'original_name' => 'a.webp',
    ]);
    $second = ProductMedia::factory()->for($product)->create([
        'path' => 'products/'.$product->id.'/images/b.webp',
        'sort_order' => 1,
        'original_name' => 'b.webp',
    ]);

    Storage::disk('public')->put($first->path, 'a');
    Storage::disk('public')->put($second->path, 'b');

    $this->get(route('landing.products.show', $product->id))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('product.name', 'Galería test')
            ->where('product.images.0.url', '/storage/'.$first->path)
            ->where('product.images.1.url', '/storage/'.$second->path)
            ->where('product.thumbnail_url', '/storage/'.$first->path));
});

test('product without images uses placeholder thumbnail on landing', function () {
    $company = Company::factory()->create(['name' => LandingPublicCatalogCompany::NAME]);
    Product::factory()->for($company)->create(['is_active' => true]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('heroProduct.thumbnail_url', '/product-placeholder.svg'));
});
