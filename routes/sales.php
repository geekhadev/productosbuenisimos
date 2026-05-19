<?php

use App\Http\Controllers\Sales\CustomersController;
use App\Http\Controllers\Sales\OrdersController;
use Illuminate\Support\Facades\Route;

Route::resource('customers', CustomersController::class)->except(['show']);

Route::resource('orders', OrdersController::class)
    ->except(['show', 'create']);
