<?php

use Illuminate\Support\Facades\Route;

if (config('rbac.disable_api', false)) {
    return;
}

$guardMiddleware = 'auth:' . config('rbac.guard', 'sanctum');
$adminAbilities = 'ability:' . implode(',', config('rbac.abilities.admin', ['admin', 'super-admin']));
$userAbilities  = 'ability:' . implode(',', config('rbac.abilities.user_update', ['admin', 'super-admin', 'user']));

Route::middleware([$guardMiddleware])->group(function () use ($adminAbilities, $userAbilities) {
    Route::middleware([$adminAbilities])->get('/rbac/ping-admin', function () {
        return response()->json(['ok' => true, 'scope' => 'admin']);
    });

    Route::middleware([$userAbilities])->get('/rbac/ping-user-update', function () {
        return response()->json(['ok' => true, 'scope' => 'user_update']);
    });
});

Route::get('rbac/ping-admin', function () {
    return response()->json([
        'success' => true,
        'message' => 'pong',
        'data' => [
            'time' => now()->toISOString(),
        ],
    ], 200);
})->middleware(['auth:sanctum']);
