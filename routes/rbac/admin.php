<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserSuspensionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PrivilegeController;
use App\Http\Controllers\Api\RolePrivilegeController;
use App\Http\Controllers\Api\UserRestoreController;
use App\Http\Controllers\Api\UserTokenController;
Route::middleware(['force_json'])->group(function () {

    $guardMiddleware = 'auth:' . config('rbac.guard', 'sanctum');
    $adminAbilities  = 'ability:' . implode(',', config('rbac.abilities.admin', ['admin', 'super-admin']));
    $userAbilities   = 'ability:' . implode(',', config('rbac.abilities.user_update', ['admin', 'super-admin', 'user']));

    Route::middleware([$guardMiddleware, 'not_suspended', 'sec_headers'])->group(function () use ($adminAbilities, $userAbilities) {

        Route::get('me', [UserController::class, 'me'])->name('me');

        // user_update scope: update users/{user}
        Route::middleware([$userAbilities])->group(function () {
            // Route::match(['put', 'patch', 'post'], 'users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::match(['put', 'patch', 'post'], 'users/{user}', [UserController::class, 'update'])
             ->middleware('can:update,user')
              ->name('users.update');

        });

        // Admin-only management (audited + throttled)
        Route::middleware([$adminAbilities, 'rbac_audit', 'throttle:rbac-admin-write'])->group(function () {

            // Users
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('users/{userId}/restore', [UserRestoreController::class, 'restore'])
    ->name('users.restore');
            // User roles
            Route::get('users/{user}/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
            Route::post('users/{user}/roles', [UserRoleController::class, 'store'])->name('users.roles.store');
            Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'destroy'])->name('users.roles.destroy');

            // Suspension
            Route::post('users/{user}/suspend', [UserSuspensionController::class, 'store'])->name('users.suspend');
            Route::delete('users/{user}/suspend', [UserSuspensionController::class, 'destroy'])->name('users.unsuspend');

            // RBAC role/privilege management
            // Route::apiResource('roles', RoleController::class)->except(['create', 'edit']);
            // Route::apiResource('privileges', PrivilegeController::class)->except(['create', 'edit']);
            Route::get('roles', [RoleController::class, 'index'])
                ->middleware('can:viewAny,App\Models\Role')
                ->name('roles.index');

            Route::post('roles', [RoleController::class, 'store'])
                ->middleware('can:create,App\Models\Role')
                ->name('roles.store');

            Route::get('roles/{role}', [RoleController::class, 'show'])
                ->middleware('can:view,role')
                ->name('roles.show');

            Route::match(['put','patch'], 'roles/{role}', [RoleController::class, 'update'])
                ->middleware('can:update,role')
                ->name('roles.update');

            Route::delete('roles/{role}', [RoleController::class, 'destroy'])
                ->middleware('can:delete,role')
                ->name('roles.destroy');
            Route::get('privileges', [PrivilegeController::class, 'index'])
                ->middleware('can:viewAny,App\Models\Privilege')
                ->name('privileges.index');

            Route::post('privileges', [PrivilegeController::class, 'store'])
                ->middleware('can:create,App\Models\Privilege')
                ->name('privileges.store');

            Route::get('privileges/{privilege}', [PrivilegeController::class, 'show'])
                ->middleware('can:view,privilege')
                ->name('privileges.show');

            Route::match(['put','patch'], 'privileges/{privilege}', [PrivilegeController::class, 'update'])
                ->middleware('can:update,privilege')
                ->name('privileges.update');

            Route::delete('privileges/{privilege}', [PrivilegeController::class, 'destroy'])
                ->middleware('can:delete,privilege')
                ->name('privileges.destroy');
            Route::post('users/{user}/suspend', [UserSuspensionController::class, 'store'])
                ->middleware('can:suspensions.manage')
                ->name('users.suspend');

            Route::delete('users/{user}/suspend', [UserSuspensionController::class, 'destroy'])
                ->middleware('can:suspensions.manage')
                ->name('users.unsuspend');


            // Role privilege management
            // Route::get('roles/{role}/privileges', [RolePrivilegeController::class, 'index'])->name('roles.privileges.index');
            // Route::post('roles/{role}/privileges', [RolePrivilegeController::class, 'store'])->name('roles.privileges.store');
            // Route::delete('roles/{role}/privileges/{privilege}', [RolePrivilegeController::class, 'destroy'])->name('roles.privileges.destroy');
              Route::get('roles/{role}/privileges', [RolePrivilegeController::class, 'index'])
                    ->middleware('can:view,role')
                    ->name('roles.privileges.index');

                Route::post('roles/{role}/privileges', [RolePrivilegeController::class, 'store'])
                    ->middleware('can:update,role')
                    ->name('roles.privileges.store');

                Route::delete('roles/{role}/privileges/{privilege}', [RolePrivilegeController::class, 'destroy'])
                    ->middleware('can:update,role')
                    ->name('roles.privileges.destroy');
                // Token management
                Route::get('users/{user}/tokens', [UserTokenController::class, 'index'])->name('users.tokens.index');
                Route::delete('users/{user}/tokens/{tokenId}', [UserTokenController::class, 'destroy'])->name('users.tokens.destroy');
                Route::delete('users/{user}/tokens', [UserTokenController::class, 'destroyAll'])->name('users.tokens.destroyAll');
                Route::post('users/{user}/tokens/rotate', [UserTokenController::class, 'rotate'])->name('users.tokens.rotate');
        });
    });
});