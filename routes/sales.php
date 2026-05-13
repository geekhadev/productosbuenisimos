<?php

use App\Http\Controllers\Sales\CustomersController;
use Illuminate\Support\Facades\Route;

Route::resource('customers', CustomersController::class)->except(['show']);
