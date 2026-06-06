<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingPublicProductController;
use App\Http\Controllers\Public\ChatbotController;
use App\Http\Controllers\Public\WhatsappMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/whatsapp-media/{mediaId}', [WhatsappMediaController::class, 'show'])
    ->whereUuid('mediaId')
    ->name('whatsapp-media.show');

Route::get('/', LandingController::class)->name('home');

Route::get('/productos/{product}', [LandingPublicProductController::class, 'show'])
    ->whereUuid('product')
    ->name('landing.products.show');

Route::prefix('chatbot')->name('chatbot.')->group(function () {
    Route::post('iniciar', [ChatbotController::class, 'iniciar'])
        ->middleware('throttle:10,1')
        ->name('iniciar');

    Route::post('nueva-conversacion', [ChatbotController::class, 'nuevaConversacion'])
        ->middleware('throttle:5,1')
        ->name('nueva-conversacion');

    Route::post('mensaje', [ChatbotController::class, 'mensaje'])
        ->middleware('throttle:20,1')
        ->name('mensaje');
});
