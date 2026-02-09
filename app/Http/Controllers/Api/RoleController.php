<?php

namespace App\Http\Controllers\Api;

use App\Auth\Services\RoleGuard;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\RoleIndexRequest;
use Illuminate\Database\Eloquent\Builder;
use Throwable;
use Illuminate\Support\Facades\Log;
class RoleController extends Controller
{
    // public function index()
    // {
    //     return Role::query()
    //         ->withCount('users')
    //         ->withCount('privileges')
    //         ->orderBy('id', 'desc')
    //         ->paginate(20);
    // }

    public function index()
    {
        try {
            $paginator = Role::query()
                ->withCount('users')
                ->withCount('privileges')
                ->orderByDesc('id')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully.',
                'data' => $paginator,
            ], 200);

        } catch (Throwable $e) {
            log::error('RoleController@index failed', ['exception' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve roles at this time.',
                'data' => null,
                'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
            ], 500);
        }
    }


    // in future we need this please so don't delete it
//      public function index(RoleIndexRequest $request)
// {
//     $perPage = (int) ($request->validated()['per_page'] ?? 20);
//     $q = $request->validated()['q'] ?? null;

//     $query = \App\Models\Role::query()
//         ->select(['id','name','slug','description','is_protected','created_at','updated_at'])
//         ->withCount('privileges');

//     if ($q) {
//         $query->where(function (Builder $b) use ($q) {
//             $b->where('name', 'like', "%{$q}%")
//               ->orWhere('slug', 'like', "%{$q}%");
//         });
//     }

//     if (!is_null($request->validated()['protected'] ?? null)) {
//         $query->where('is_protected', $request->boolean('protected'));
//     }

//     $roles = $query->latest('id')->paginate($perPage)->appends($request->query());

//     return response()->json([
//         'success' => true,
//         'message' => 'Roles fetched successfully.',
//         'data' => [
//             'items' => $roles->items(),
//             'meta' => [
//                 'current_page' => $roles->currentPage(),
//                 'per_page' => $roles->perPage(),
//                 'total' => $roles->total(),
//                 'last_page' => $roles->lastPage(),
//             ],
//             'filters' => $request->validated(),
//         ],
//     ], 200);
// }

    public function store(RoleStoreRequest $request)
{
    $data = $request->validated();

    try {
        $role = Role::create($data);

        if (class_exists(\App\Support\Audit::class)) {
            \App\Support\Audit::log('roles.create', $role, $data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => ['role' => $role],
        ], 201);

    } catch (Throwable $e) {
        Log::error('RoleController@store failed', [
            'data' => $data,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unable to create role at this time.',
            'data' => null,
            'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], 500);
    }
}


    public function show(Role $role)
    {
        $role->load(['privileges:id,name,slug,description']);
        return response()->json($role);
    }

    public function update(RoleUpdateRequest $request, Role $role, RoleGuard $guard)
{
    $data = $request->validated();

    try {
        $guard->ensureNotProtected($role);

        $role->update($data);

        if (method_exists($role, 'flushUsersRbacCache')) {
            $role->flushUsersRbacCache();
        }

        if (class_exists(\App\Support\Audit::class)) {
            \App\Support\Audit::log('roles.update', $role, $data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => ['role' => $role->fresh()],
        ], 200);

    } catch (Throwable $e) {
        Log::error('RoleController@update failed', [
            'role_id' => $role->id,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unable to update role at this time.',
            'data' => null,
            'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], 500);
    }
}


    public function destroy(Role $role, RoleGuard $guard)
{
    try {
        $guard->ensureNotProtected($role);

        if (method_exists($role, 'flushUsersRbacCache')) {
            $role->flushUsersRbacCache();
        }

        $role->delete();

        if (class_exists(\App\Support\Audit::class)) {
            \App\Support\Audit::log('roles.delete', $role);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
            'data' => null,
        ], 200);

    } catch (Throwable $e) {
        Log::error('RoleController@destroy failed', [
            'role_id' => $role->id,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unable to delete role at this time.',
            'data' => null,
            'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], 500);
    }
}

}
