<?php

use App\Http\Controllers\Webhooks\TwilioWhatsappController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/whatsapp/twilio', TwilioWhatsappController::class)
    ->name('webhook.whatsapp.twilio');
