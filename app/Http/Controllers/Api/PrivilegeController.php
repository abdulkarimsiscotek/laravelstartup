<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrivilegeStoreRequest;
use App\Http\Requests\PrivilegeUpdateRequest;
use App\Models\Privilege;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PrivilegeController extends Controller
{
    public function index()
    {
        try {
            $paginator = Privilege::query()
                ->withCount('roles')
                ->orderByDesc('id')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Privileges fetched successfully.',
                'data' => $paginator,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Privilege index failed', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch privileges.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(PrivilegeStoreRequest $request)
    {
        try {
            $privilege = Privilege::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Privilege created successfully.',
                'data' => $privilege,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Privilege store failed', [
                'payload' => $request->validated(),
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create privilege.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(Privilege $privilege)
    {
        try {
            $privilege->load(['roles:id,name,slug']);

            return response()->json([
                'success' => true,
                'message' => 'Privilege fetched successfully.',
                'data' => $privilege,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Privilege show failed', [
                'privilege_id' => $privilege->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch privilege.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(PrivilegeUpdateRequest $request, Privilege $privilege)
    {
        try {
            $privilege->update($request->validated());

            // NOTE: user caches are flushed on role changes/role privilege changes elsewhere
            return response()->json([
                'success' => true,
                'message' => 'Privilege updated successfully.',
                'data' => $privilege->fresh(),
            ], 200);
        } catch (Throwable $e) {
            Log::error('Privilege update failed', [
                'privilege_id' => $privilege->id,
                'payload' => $request->validated(),
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update privilege.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Privilege $privilege)
    {
        try {
            DB::transaction(function () use ($privilege) {
                // Detach from roles first to keep pivots clean
                $privilege->roles()->detach();
                $privilege->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Privilege deleted successfully.',
                'data' => null,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Privilege destroy failed', [
                'privilege_id' => $privilege->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete privilege.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}