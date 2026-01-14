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
class RoleController extends Controller
{
    public function index()
    {
        return Role::query()
            ->withCount('users')
            ->withCount('privileges')
            ->orderBy('id', 'desc')
            ->paginate(20);
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
        $role = Role::create($request->validated());
        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        $role->load(['privileges:id,name,slug,description']);
        return response()->json($role);
    }

    public function update(RoleUpdateRequest $request, Role $role, RoleGuard $guard)
    {
        // Prevent modifications to protected roles (slug-based and flag-based)
        $guard->ensureNotProtected($role);

        $role->update($request->validated());

        // Flush cache for users of this role (Module 3 method)
        if (method_exists($role, 'flushUsersRbacCache')) {
            $role->flushUsersRbacCache();
        }

        return response()->json($role);
    }

    public function destroy(Role $role, RoleGuard $guard)
    {
        $guard->ensureNotProtected($role);

        if (method_exists($role, 'flushUsersRbacCache')) {
            $role->flushUsersRbacCache();
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }
}