<?php

use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok', 'time' => now()->toIso8601String()]);

Route::prefix('quiz')->group(function () {
    Route::get('/questions', [QuestionController::class, 'index'])->name('quiz.questions.index');
});

Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

Route::get('/user', fn (Request $request) => $request->user())->middleware('auth:sanctum');
