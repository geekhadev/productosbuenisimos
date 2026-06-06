<?php

use App\Http\Controllers\Test\ContraEntregaTestController;
use Illuminate\Support\Facades\Route;

Route::get('test/contraentrega', ContraEntregaTestController::class)
    ->name('test.contraentrega');
