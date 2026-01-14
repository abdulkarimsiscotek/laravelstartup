<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RbacMetaController;
Route::middleware(['force_json'])->group(function () {
Route::get('rbac/info', [RbacMetaController::class, 'info'])->name('rbac.info');
Route::get('rbac/version', [RbacMetaController::class, 'version'])->name('rbac.version');
Route::get('rbac/docs', [RbacMetaController::class, 'docs'])->name('rbac.docs');
});
