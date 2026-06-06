<?php

use App\Http\Controllers\Sales\AgentConfigController;
use App\Http\Controllers\Sales\ConversationsController;
use App\Http\Controllers\Sales\CustomersController;
use App\Http\Controllers\Sales\LeadsController;
use App\Http\Controllers\Sales\OrderFulfillmentController;
use App\Http\Controllers\Sales\OrdersController;
use Illuminate\Support\Facades\Route;

Route::get('agent', [AgentConfigController::class, 'edit'])->name('agent.edit');
Route::put('agent', [AgentConfigController::class, 'update'])->name('agent.update');

Route::get('conversations', [ConversationsController::class, 'index'])
    ->name('conversations.index');
Route::get('conversations/{conversation}', [ConversationsController::class, 'show'])
    ->name('conversations.show');
Route::patch('conversations/{conversation}/toggle-agent', [ConversationsController::class, 'toggleAgent'])
    ->name('conversations.toggle-agent');
Route::post('conversations/{conversation}/operator-message', [ConversationsController::class, 'operatorMessage'])
    ->name('conversations.operator-message');

Route::resource('customers', CustomersController::class)->except(['show']);

Route::resource('leads', LeadsController::class)->except(['show']);

Route::post('orders/fulfillment/send-to-contraentrega', [OrderFulfillmentController::class, 'sendToContraEntrega'])
    ->name('orders.fulfillment.send-to-contraentrega');

Route::resource('orders', OrdersController::class)
    ->except(['show', 'create']);
