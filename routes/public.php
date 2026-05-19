<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingPublicProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

Route::get('/productos/{product}', [LandingPublicProductController::class, 'show'])
    ->whereUuid('product')
    ->name('landing.products.show');
