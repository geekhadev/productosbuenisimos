<?php

use App\Http\Controllers\Shared\CountriesController;
use App\Http\Controllers\Shared\StatesController;
use Illuminate\Support\Facades\Route;

Route::resource('countries', CountriesController::class)->except(['show']);
Route::resource('states', StatesController::class)->except(['show']);
