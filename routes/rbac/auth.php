<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
Route::middleware(['force_json'])->group(function () {
Route::prefix('auth')->group(function () {
    // Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('auth.login');


    Route::middleware(['auth:sanctum', 'not_suspended','sec_headers'])->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('auth.logoutAll');
    });
});
});
