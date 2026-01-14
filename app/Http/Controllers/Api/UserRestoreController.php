<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserRestoreController extends Controller
{
    public function restore(int $userId)
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);

            DB::transaction(function () use ($user) {
                $user->restore();

                if (method_exists($user, 'flushRbacCache')) {
                    $user->flushRbacCache();
                }

                \App\Support\Audit::log('users.restore', $user);
            });

            return response()->json([
                'success' => true,
                'message' => 'User restored successfully.',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email']),
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('User restore failed', [
                'user_id' => $userId,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore user.',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}