<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Http\Requests\UserIndexRequest;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    /**
     * Admin: list users.
     */
     // in future we need this please so don't delete it
//     public function index(UserIndexRequest $request)
// {
//     try {
//         $perPage = (int) ($request->validated()['per_page'] ?? 20);
//         $q = $request->validated()['q'] ?? null;

//         $query = \App\Models\User::query()
//             ->select(['id','name','email','email_verified_at','created_at','updated_at','deleted_at']);

//         // Soft deletes filters
//         if ($request->boolean('only_trashed')) {
//             $query->onlyTrashed();
//         } elseif ($request->boolean('with_trashed')) {
//             $query->withTrashed();
//         }

//         // Search
//         if ($q) {
//             $query->where(function (Builder $b) use ($q) {
//                 $b->where('name', 'like', "%{$q}%")
//                   ->orWhere('email', 'like', "%{$q}%");
//             });
//         }

//         // Filter by role slug
//         if ($roleSlug = ($request->validated()['role'] ?? null)) {
//             $query->whereHas('roles', function (Builder $b) use ($roleSlug) {
//                 $b->where('slug', $roleSlug);
//             });
//         }

//         // Filter by suspension (if you have suspension relation/table)
//         if (!is_null($request->validated()['suspended'] ?? null)) {
//             $wantSuspended = $request->boolean('suspended');

//             // If your system has a suspensions table/relation, adapt here.
//             // Common pattern: users.suspended_at nullable.
//             if (schema()->hasColumn('users', 'suspended_at')) {
//                 $wantSuspended
//                     ? $query->whereNotNull('suspended_at')
//                     : $query->whereNull('suspended_at');
//             }
//         }

//         // Eager load roles (light)
//         $query->with(['roles:id,slug,name']);

//         $users = $query->latest('id')->paginate($perPage)->appends($request->query());

//         return response()->json([
//             'success' => true,
//             'message' => 'Users fetched successfully.',
//             'data' => [
//                 'items' => $users->items(),
//                 'meta' => [
//                     'current_page' => $users->currentPage(),
//                     'per_page' => $users->perPage(),
//                     'total' => $users->total(),
//                     'last_page' => $users->lastPage(),
//                 ],
//                 'filters' => $request->validated(),
//             ],
//         ], 200);
//     } catch (\Throwable $e) {
//         \Log::error('User index failed', ['exception' => $e->getMessage()]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to fetch users.',
//             'data' => null,
//             'error' => config('app.debug') ? $e->getMessage() : null,
//         ], 500);
//     }
// }

    // public function index()
    // {
    //     try {
    //         $paginator = User::query()
    //             ->with(['roles:id,name,slug'])
    //             ->orderByDesc('id')
    //             ->paginate(20);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Users retrieved successfully.',
    //             'data' => $paginator,
    //         ], 200);
    //     } catch (Throwable $e) {
    //         Log::error('UserController@index failed', [
    //             'exception' => $e->getMessage(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unable to retrieve users at this time.',
    //             'data' => null,
    //             'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
    //         ], 500);
    //     }
    // }
//     public function index(Request $request)
// {
//     try {
//         $query = User::query()
//             ->select(['id','name','email','email_verified_at','created_at','updated_at','deleted_at'])
//             ->with(['roles:id,name,slug'])
//             ->orderByDesc('id');

//         if ($request->boolean('only_trashed')) {
//             $query->onlyTrashed();
//         } elseif ($request->boolean('with_trashed')) {
//             $query->withTrashed();
//         }

//         if ($q = $request->query('q')) {
//             $query->where(function ($b) use ($q) {
//                 $b->where('name', 'like', "%{$q}%")
//                   ->orWhere('email', 'like', "%{$q}%");
//             });
//         }

//         $paginator = $query->paginate(20)->appends($request->query());

//         return response()->json([
//             'success' => true,
//             'message' => 'Users retrieved successfully.',
//             'data' => $paginator,
//         ], 200);

//     } catch (Throwable $e) {
//         Log::error('UserController@index failed', [
//             'exception' => $e->getMessage(),
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Unable to retrieve users at this time.',
//             'data' => null,
//             'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
//         ], 500);
//     }
// }

    public function index(UserIndexRequest $request)
{
    $filters = $request->validated();

    try {
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['per_page'] ?? 20);
        $q = $filters['q'] ?? null;
        $roleSlug = $filters['role'] ?? null;

        $query = User::query()
            ->select(['id','name','email','email_verified_at','created_at','updated_at','deleted_at'])
            ->with(['roles:id,name,slug']);

        // Soft delete filters
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        // Search (name/email)
        if ($q) {
            $query->where(function (Builder $b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Role filter (by slug)
        if ($roleSlug) {
            $query->whereHas('roles', function (Builder $b) use ($roleSlug) {
                $b->where('slug', $roleSlug);
            });
        }

        // Suspended filter (active suspensions in user_suspensions)
        if (!is_null($filters['suspended'] ?? null)) {
            $wantSuspended = $request->boolean('suspended');

            if ($wantSuspended) {
                $query->whereHas('suspensions', function (Builder $b) {
                    $b->where(function ($q) {
                        $q->whereNull('suspended_until')
                          ->orWhere('suspended_until', '>', now());
                    });
                });
            } else {
                $query->whereDoesntHave('suspensions', function (Builder $b) {
                    $b->where(function ($q) {
                        $q->whereNull('suspended_until')
                          ->orWhere('suspended_until', '>', now());
                    });
                });
            }
        }

        $paginator = $query
            ->orderByDesc('id')
            ->paginate($limit, ['*'], 'page', $page);

        // Transform rows (keep it simple for AG-Grid)
        $rows = collect($paginator->items())->map(function (User $u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'email_verified_at' => $u->email_verified_at,
                'created_at' => $u->created_at,
                'updated_at' => $u->updated_at,
                'deleted_at' => $u->deleted_at,
                'is_suspended' => method_exists($u, 'isSuspended') ? $u->isSuspended() : false,
                'roles' => $u->roles->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'slug' => $r->slug,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'User list fetched successfully.',
            'data' => $rows,
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
            ],
        ], 200);

    } catch (Throwable $e) {
        Log::error('UserController@index failed', [
            'filters' => $filters,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch user list.',
            'data' => [],
            'meta' => [
                'total' => 0,
                'page' => (int) ($filters['page'] ?? 1),
                'limit' => (int) ($filters['per_page'] ?? 20),
            ],
            'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], 500);
    }
}


    /**
     * Public or Admin: create user (registration-like).
     * Assign default role slug from config.
     */
    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();

        try {
            /** @var \App\Models\User $user */
            $user = DB::transaction(function () use ($data) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);

                $defaultSlug = config('rbac.default_user_role_slug', 'user');

                // Fail fast if misconfigured: this is an operational issue, not a user issue.
                $defaultRole = Role::where('slug', $defaultSlug)->firstOrFail();

                $user->roles()->syncWithoutDetaching([$defaultRole->id]);

                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                return $user;
            });

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'data' => [
                    'user' => $user->load('roles:id,name,slug'),
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('UserController@store failed', [
                'email' => $data['email'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create user at this time.',
                'data' => null,
                'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
            ], 500);
        }
    }

    /**
     * Admin: show user.
     */
    public function show(User $user)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully.',
                'data' => [
                    'user' => $user->load('roles:id,name,slug'),
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('UserController@show failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve user at this time.',
                'data' => null,
                'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
            ], 500);
        }
    }

    /**
     * Admin or user_update scope: update user.
     * NOTE: Route middleware controls who can call this.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();

        try {
            if (array_key_exists('password', $data) && $data['password']) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => [
                    'user' => $user->fresh()->load('roles:id,name,slug'),
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('UserController@update failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update user at this time.',
                'data' => null,
                'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
            ], 500);
        }
    }

    /**
     * Admin: soft-delete user (revoke tokens, flush cache, audit).
     */
    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {

                // Revoke tokens (personal_access_tokens)
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                // Soft delete (requires SoftDeletes on User model)
                $user->delete();

                // Flush RBAC cache (if available)
                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                // Audit (safe call)
                if (class_exists(\App\Support\Audit::class) && method_exists(\App\Support\Audit::class, 'log')) {
                    \App\Support\Audit::log('users.delete', $user);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
                'data' => null,
            ], 200);
        } catch (Throwable $e) {
            Log::error('UserController@destroy failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete user at this time.',
                'data' => null,
                'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
            ], 500);
        }
    }

    /**
     * Authenticated: current user profile.
     */
    public function me(Request $request)
    {
        try {
            $authUser = $request->user();

            if (!$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'data' => null,
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Authenticated user retrieved successfully.',
                'data' => [
                    'user' => $authUser->load('roles:id,name,slug'),
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('UserController@me failed', [
                'user_id' => optional($request->user())->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve authenticated user at this time.',
                'data' => null,
                'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
            ], 500);
        }
    }
}