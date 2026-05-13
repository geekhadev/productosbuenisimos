<?php

use App\Http\Controllers\Api\CompanyAssignableRolesController;
use App\Http\Controllers\Api\UserCompanyRoleAssignmentsController;
use App\Http\Controllers\CompanySelectionController;
use App\Http\Middleware\EnsureCompanySelected;
use Illuminate\Support\Facades\Route;

require __DIR__.'/public.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('company-selection', [CompanySelectionController::class, 'index'])
        ->name('company-selection.index');
    Route::post('company-selection', [CompanySelectionController::class, 'store'])
        ->name('company-selection.store');
});

Route::middleware(['auth', 'verified', EnsureCompanySelected::class])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('administration')->name('administration.')->group(function () {
        require __DIR__.'/administration.php';
    });

    Route::prefix('configuration')->name('configuration.')->group(function () {
        require __DIR__.'/configuration.php';
    });

    Route::prefix('shared')->name('shared.')->group(function () {
        require __DIR__.'/shared.php';
    });

    Route::prefix('stock')->name('stock.')->group(function () {
        require __DIR__.'/stock.php';
    });

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('companies/{company}/roles', CompanyAssignableRolesController::class)
            ->name('companies.roles.index');

        Route::get('users/{user}/company-role-assignments', [UserCompanyRoleAssignmentsController::class, 'index'])
            ->name('users.company-role-assignments.index');
        Route::post('users/{user}/company-role-assignments', [UserCompanyRoleAssignmentsController::class, 'store'])
            ->name('users.company-role-assignments.store');
        Route::delete('users/{user}/company-role-assignments/{assignment}', [UserCompanyRoleAssignmentsController::class, 'destroy'])
            ->whereNumber('assignment')
            ->name('users.company-role-assignments.destroy');
    });
});

require __DIR__.'/settings.php';
