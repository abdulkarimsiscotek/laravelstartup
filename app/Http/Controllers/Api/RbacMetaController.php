<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RbacMetaController extends Controller
{
    public function info()
    {
        return response()->json([
            'success' => true,
            'message' => 'RBAC subsystem info.',
            'data' => [
                'version' => config('rbac.version', '1.0.0'),
                'guard' => config('rbac.guard', 'sanctum'),
                'cache' => config('rbac.cache'),
                'disable_api' => config('rbac.disable_api', false),
            ],
        ]);
    }

    public function version()
    {
        return response()->json([
            'success' => true,
            'message' => 'RBAC version.',
            'data' => [
                'version' => config('rbac.version', '1.0.0'),
            ],
        ]);
    }

    public function docs()
    {
        // Simple structured docs for Postman/React devs
        return response()->json([
            'success' => true,
            'message' => 'RBAC API docs.',
            'data' => [
                'auth' => [
                    'POST /api/auth/login',
                    'POST /api/auth/logout',
                    'POST /api/auth/logout-all',
                    'GET /api/auth/me',
                ],
                'meta' => [
                    'GET /api/rbac/info',
                    'GET /api/rbac/version',
                    'GET /api/rbac/docs',
                ],
                'admin' => [
                    'GET /api/users',
                    'GET /api/users/{user}',
                    'DELETE /api/users/{user}',
                    'POST /api/users/{user}/roles',
                    'DELETE /api/users/{user}/roles/{role}',

                    'GET /api/roles',
                    'POST /api/roles',
                    'PATCH /api/roles/{role}',
                    'DELETE /api/roles/{role}',

                    'GET /api/privileges',
                    'POST /api/privileges',
                    'PATCH /api/privileges/{privilege}',
                    'DELETE /api/privileges/{privilege}',

                    'GET /api/roles/{role}/privileges',
                    'POST /api/roles/{role}/privileges',
                    'DELETE /api/roles/{role}/privileges/{privilege}',

                    'POST /api/users/{user}/suspend',
                    'DELETE /api/users/{user}/suspend',
                ],
            ],
        ]);
    }
}