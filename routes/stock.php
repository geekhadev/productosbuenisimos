<?php

use App\Http\Controllers\Stock\ProductImagesController;
use App\Http\Controllers\Stock\ProductSimilarProductsController;
use App\Http\Controllers\Stock\ProductsController;
use App\Http\Controllers\Stock\ProductVideoController;
use Illuminate\Support\Facades\Route;

Route::patch('products/{product}/deactivate', [ProductsController::class, 'deactivate'])
    ->name('products.deactivate');

Route::post('products/{product}/images', [ProductImagesController::class, 'store'])
    ->name('products.images.store');
Route::put('products/{product}/images/reorder', [ProductImagesController::class, 'reorder'])
    ->name('products.images.reorder');
Route::delete('products/{product}/images/{media}', [ProductImagesController::class, 'destroy'])
    ->name('products.images.destroy');

Route::post('products/{product}/video', [ProductVideoController::class, 'store'])
    ->name('products.video.store');
Route::delete('products/{product}/video', [ProductVideoController::class, 'destroy'])
    ->name('products.video.destroy');

Route::get('products/{product}/similar/search', [ProductSimilarProductsController::class, 'search'])
    ->name('products.similar.search');
Route::post('products/{product}/similar', [ProductSimilarProductsController::class, 'store'])
    ->name('products.similar.store');
Route::delete('products/{product}/similar/{similarProduct}', [ProductSimilarProductsController::class, 'destroy'])
    ->name('products.similar.destroy');

Route::resource('products', ProductsController::class)->except(['show']);
