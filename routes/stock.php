<?php

use App\Http\Controllers\Stock\ProductsController;
use Illuminate\Support\Facades\Route;

Route::patch('products/{product}/deactivate', [ProductsController::class, 'deactivate'])
    ->name('products.deactivate');
Route::resource('products', ProductsController::class)->except(['show']);
