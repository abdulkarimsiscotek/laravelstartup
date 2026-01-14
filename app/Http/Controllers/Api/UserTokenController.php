<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserTokenController extends Controller
{
    public function index(User $user)
    {
        try {
            $tokens = $user->tokens()
                ->select(['id', 'name', 'last_used_at', 'created_at', 'expires_at'])
                ->latest('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'User tokens fetched successfully.',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email']),
                    'tokens' => $tokens,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('Token index failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tokens.',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(User $user, int $tokenId)
    {
        try {
            $deleted = $user->tokens()->where('id', $tokenId)->delete();

            \App\Support\Audit::log('users.tokens.revoke', $user, [
                'token_id' => $tokenId,
                'deleted' => (bool) $deleted,
            ]);

            return response()->json([
                'success' => true,
                'message' => $deleted ? 'Token revoked successfully.' : 'Token not found.',
                'data' => [
                    'user_id' => $user->id,
                    'token_id' => $tokenId,
                    'revoked' => (bool) $deleted,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('Token destroy failed', [
                'user_id' => $user->id,
                'token_id' => $tokenId,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke token.',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroyAll(User $user)
    {
        try {
            $count = $user->tokens()->count();
            $user->tokens()->delete();

            \App\Support\Audit::log('users.tokens.revoke_all', $user, [
                'revoked_count' => $count,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All tokens revoked successfully.',
                'data' => [
                    'user_id' => $user->id,
                    'revoked_count' => $count,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('Token destroyAll failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke all tokens.',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function rotate(User $user)
    {
        // Rotate means: revoke all existing tokens, then issue a new one.
        // This is an admin incident response tool.

        try {
            $plainTextToken = null;

            DB::transaction(function () use ($user, &$plainTextToken) {
                $user->tokens()->delete();

                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                // Abilities: match your existing token ability behavior.
                // If you already have a method like $user->tokenAbilities(), use it.
                $abilities = [];

                $plainTextToken = $user->createToken('rotated-admin', $abilities)->plainTextToken;

                \App\Support\Audit::log('users.tokens.rotate', $user, [
                    'new_token_name' => 'rotated-admin',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Token rotated successfully.',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email']),
                    'token' => $plainTextToken,
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('Token rotate failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to rotate token.',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}