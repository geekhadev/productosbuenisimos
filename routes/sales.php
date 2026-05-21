<?php

use App\Http\Controllers\Sales\AgentConfigController;
use App\Http\Controllers\Sales\CustomersController;
use App\Http\Controllers\Sales\LeadsController;
use App\Http\Controllers\Sales\OrdersController;
use Illuminate\Support\Facades\Route;

Route::get('agent', [AgentConfigController::class, 'edit'])->name('agent.edit');
Route::put('agent', [AgentConfigController::class, 'update'])->name('agent.update');

Route::resource('customers', CustomersController::class)->except(['show']);

Route::resource('leads', LeadsController::class)->except(['show']);

Route::resource('orders', OrdersController::class)
    ->except(['show', 'create']);
