<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSuspension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserSuspensionController extends Controller
{
    /**
     * Suspend a user.
     * POST /api/users/{user}/suspend
     */
    public function store(Request $request, User $user)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            // null means indefinite suspension
            'suspended_until' => ['nullable', 'date'],
        ]);

        try {
            $result = DB::transaction(function () use ($user, $data) {
                // Prevent duplicate "active" suspensions (optional but recommended)
                $hasActive = method_exists($user, 'isSuspended') ? $user->isSuspended() : false;
                if ($hasActive) {
                    $active = method_exists($user, 'activeSuspension') ? $user->activeSuspension() : null;

                    return response()->json([
                        'message' => 'User is already suspended.',
                        'reason' => $active?->reason,
                        'suspended_until' => optional($active?->suspended_until)->toISOString(),
                    ], 409);
                }

                /** @var \App\Models\UserSuspension $suspension */
                $suspension = UserSuspension::create([
                    'user_id' => $user->id,
                    'reason' => $data['reason'] ?? null,
                    'suspended_at' => now(),
                    'suspended_until' => $data['suspended_until'] ?? null,
                ]);

                // Second table operation: immediately revoke tokens so the user is kicked out
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                // Optional: flush RBAC cache if you cache access decisions/abilities per user
                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                return response()->json([
                    'message' => 'User suspended successfully.',
                    'data' => [
                        'user_id' => $user->id,
                        'reason' => $suspension->reason,
                        'suspended_at' => optional($suspension->suspended_at)->toISOString(),
                        'suspended_until' => optional($suspension->suspended_until)->toISOString(),
                    ],
                ], 201);
            });

            return $result;
        } catch (Throwable $e) {
            Log::error('User suspension store failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
        'success' => false,
        'message' => 'Failed to suspend user.',
        'error' => config('app.debug') ? $e->getMessage() : null,
    ], 500);
        }
    }

    /**
     * Unsuspend a user (end the active suspension).
     * DELETE /api/users/{user}/suspend
     */
    public function destroy(Request $request, User $user)
    {
        try {
            $result = DB::transaction(function () use ($user) {
                // Find the most recent active suspension
                $active = method_exists($user, 'activeSuspension')
                    ? $user->activeSuspension()
                    : $user->suspensions()
                        ->where(function ($q) {
                            $q->whereNull('suspended_until')
                                ->orWhere('suspended_until', '>', now());
                        })
                        ->latest('suspended_at')
                        ->first();

                if (!$active) {
                    return response()->json([
                        'message' => 'User is not currently suspended.',
                    ], 404);
                }

                // Non-destructive: end the suspension by setting suspended_until to now
                // (If your business logic prefers delete(), replace with $active->delete();)
                $active->update([
                    'suspended_until' => now(),
                ]);

                // Second table operation: revoke tokens again (optional but keeps state consistent)
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                // Optional: flush RBAC cache
                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                return response()->json([
                    'message' => 'User unsuspended successfully.',
                    'data' => [
                        'user_id' => $user->id,
                        'ended_at' => now()->toISOString(),
                    ],
                ], 200);
            });

            return $result;
        } catch (Throwable $e) {
            Log::error('User suspension destroy failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
        'success' => false,
        'message' => 'Failed to suspend user.',
        'error' => config('app.debug') ? $e->getMessage() : null,
    ], 500);
        }
    }
}
