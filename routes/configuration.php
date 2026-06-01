<?php

use App\Http\Controllers\Configuration\AiProvidersController;
use App\Http\Controllers\Configuration\CompaniesController;
use App\Http\Controllers\Configuration\FulfillmentProvidersController;
use App\Http\Controllers\Configuration\RolesController;
use App\Http\Controllers\Configuration\UserController;
use App\Http\Controllers\Configuration\WhatsappProvidersController;
use Illuminate\Support\Facades\Route;

Route::resource('companies', CompaniesController::class)->except(['show']);

Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

Route::get('ai-providers', [AiProvidersController::class, 'edit'])->name('ai-providers.edit');
Route::put('ai-providers/{provider}/credential', [AiProvidersController::class, 'updateCredential'])
    ->name('ai-providers.credential.update');

Route::get('fulfillment-providers', [FulfillmentProvidersController::class, 'edit'])->name('fulfillment-providers.edit');
Route::put('fulfillment-providers/{provider}/credential', [FulfillmentProvidersController::class, 'updateCredential'])
    ->name('fulfillment-providers.credential.update');

Route::get('whatsapp-providers', [WhatsappProvidersController::class, 'edit'])->name('whatsapp-providers.edit');
Route::put('whatsapp-providers/{provider}/credential', [WhatsappProvidersController::class, 'updateCredential'])
    ->name('whatsapp-providers.credential.update');

Route::get('roles', [RolesController::class, 'index'])->name('roles.index');
Route::post('roles', [RolesController::class, 'store'])->name('roles.store');
Route::put('roles/sync', [RolesController::class, 'sync'])->name('roles.sync');
Route::delete('roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
