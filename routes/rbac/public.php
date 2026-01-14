<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::post('users', [UserController::class, 'store'])->name('users.store.public');