<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleStoreRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserRoleController extends Controller
{
    public function index(User $user)
    {
        try {
            $roles = $user->roles()
                ->select('roles.id', 'roles.name', 'roles.slug')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'User roles fetched successfully.',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email']),
                    'roles' => $roles,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('UserRole index failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user roles.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(UserRoleStoreRequest $request, User $user)
    {
        $roleIds = $request->validated()['role_ids'];

        try {
            DB::transaction(function () use ($user, $roleIds) {
                $user->roles()->syncWithoutDetaching($roleIds);

                // Flush cached privileges and revoke tokens (abilities must refresh)
                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            });

            $roles = $user->roles()
                ->select('roles.id', 'roles.name', 'roles.slug')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles attached successfully.',
                'data' => [
                    'user_id' => $user->id,
                    'roles' => $roles,
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('UserRole store failed', [
                'user_id' => $user->id,
                'role_ids' => $roleIds,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to attach roles.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(User $user, Role $role)
    {
        try {
            DB::transaction(function () use ($user, $role) {
                $user->roles()->detach($role->id);

                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            });

            $roles = $user->roles()
                ->select('roles.id', 'roles.name', 'roles.slug')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Role detached successfully.',
                'data' => [
                    'user_id' => $user->id,
                    'detached_role_id' => $role->id,
                    'roles' => $roles,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('UserRole destroy failed', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to detach role.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}