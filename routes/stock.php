<?php

use App\Http\Controllers\Stock\ProductImagesController;
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

Route::resource('products', ProductsController::class)->except(['show']);
