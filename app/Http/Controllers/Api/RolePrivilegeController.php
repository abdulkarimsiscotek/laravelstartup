<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Privilege;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RolePrivilegeController extends Controller
{
    public function index(Role $role)
    {
        try {
            $role->load(['privileges:id,name,slug,description']);

            return response()->json([
                'success' => true,
                'message' => 'Role privileges fetched successfully.',
                'data' => [
                    'role' => $role->only(['id', 'name', 'slug']),
                    'privileges' => $role->privileges,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('RolePrivilege index failed', [
                'role_id' => $role->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role privileges.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(Request $request, Role $role)
    {
        $validated = $request->validate([
            'privilege_ids' => ['required', 'array', 'min:1'],
            'privilege_ids.*' => ['integer', 'exists:privileges,id'],
        ]);

        try {
            DB::transaction(function () use ($role, $validated) {
                $role->privileges()->syncWithoutDetaching($validated['privilege_ids']);

                // Flush user RBAC cache for users in this role
                if (method_exists($role, 'flushUsersRbacCache')) {
                    $role->flushUsersRbacCache();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Privileges attached successfully.',
                'data' => [
                    'role_id' => $role->id,
                    'privilege_ids' => $validated['privilege_ids'],
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('RolePrivilege store failed', [
                'role_id' => $role->id,
                'privilege_ids' => $validated['privilege_ids'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to attach privileges.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Role $role, Privilege $privilege)
    {
        try {
            DB::transaction(function () use ($role, $privilege) {
                $role->privileges()->detach($privilege->id);

                if (method_exists($role, 'flushUsersRbacCache')) {
                    $role->flushUsersRbacCache();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Privilege detached successfully.',
                'data' => [
                    'role_id' => $role->id,
                    'privilege_id' => $privilege->id,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('RolePrivilege destroy failed', [
                'role_id' => $role->id,
                'privilege_id' => $privilege->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to detach privilege.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}