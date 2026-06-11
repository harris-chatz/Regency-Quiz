<?php

use App\Http\Controllers\Admin\LeadsAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/landing.html'));

Route::middleware('admin.basic')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [LeadsAdminController::class, 'index'])->name('leads.index');
        Route::get('/leads/export.csv', [LeadsAdminController::class, 'exportCsv'])
            ->name('leads.export');
    });
