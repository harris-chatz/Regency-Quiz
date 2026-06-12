<?php

use App\Http\Controllers\Admin\LeadsAdminController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'quiz.intro');
Route::view('/intro', 'quiz.intro');
Route::view('/terms', 'quiz.terms');
Route::view('/question-1', 'quiz.q1');
Route::view('/question-2', 'quiz.q2');
Route::view('/question-3', 'quiz.q3');
Route::view('/result-1', 'quiz.result1');
Route::view('/result-2', 'quiz.result2');
Route::view('/result-3', 'quiz.result3');
Route::view('/submit', 'quiz.submit');
Route::view('/exit', 'quiz.exit');

Route::middleware('admin.basic')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [LeadsAdminController::class, 'index'])->name('leads.index');
        Route::get('/leads/export.csv', [LeadsAdminController::class, 'exportCsv'])
            ->name('leads.export');
    });
