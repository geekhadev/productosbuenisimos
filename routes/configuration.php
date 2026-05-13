<?php

use App\Http\Controllers\Configuration\CompaniesController;
use App\Http\Controllers\Configuration\RolesController;
use App\Http\Controllers\Configuration\UserController;
use Illuminate\Support\Facades\Route;

Route::resource('companies', CompaniesController::class)->except(['show']);

Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

Route::get('roles', [RolesController::class, 'index'])->name('roles.index');
Route::post('roles', [RolesController::class, 'store'])->name('roles.store');
Route::put('roles/sync', [RolesController::class, 'sync'])->name('roles.sync');
Route::delete('roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
