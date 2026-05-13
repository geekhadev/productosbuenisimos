<?php

use App\Http\Controllers\Administration\ModulesController;
use App\Http\Controllers\Administration\PermissionsController;
use App\Http\Controllers\Administration\SystemsController;
use Illuminate\Support\Facades\Route;

Route::resource('systems', SystemsController::class)->except(['show']);
Route::resource('modules', ModulesController::class)->except(['show']);
Route::resource('permissions', PermissionsController::class)->except(['show']);
