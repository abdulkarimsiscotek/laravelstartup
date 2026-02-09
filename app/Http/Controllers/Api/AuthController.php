<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
        'device_name' => ['nullable', 'string', 'max:255'],
    ]);

    try {
        /** @var \App\Models\User|null $user */
        $user = User::where('email', $validated['email'])->first();

        // Block login if user not found, password mismatch, or soft-deleted
        if (
            !$user ||
            (method_exists($user, 'trashed') && $user->trashed()) ||
            !Hash::check($validated['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Block login if suspended
        if (method_exists($user, 'isSuspended') && $user->isSuspended()) {
            $active = method_exists($user, 'activeSuspension') ? $user->activeSuspension() : null;

            return response()->json([
                'success' => false,
                'message' => 'Account suspended.',
                'data' => [
                    'reason' => $active?->reason,
                    'suspended_until' => optional($active?->suspended_until)->toISOString(),
                ],
            ], 403);
        }

        $deviceName = $validated['device_name'] ?? 'api';

        $token = DB::transaction(function () use ($user, $deviceName) {
            if (config('rbac.delete_previous_access_tokens_on_login')) {
                $user->tokens()->delete();
            }

            $abilities = app(\App\Auth\Services\TokenAbilityService::class)->abilitiesFor($user);

            return $user->createToken($deviceName, $abilities);
        });

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $user->load('roles:id,name,slug'),
            ],
        ], 200);

    } catch (ValidationException $e) {
        throw $e;
    } catch (Throwable $e) {
        Log::error('Login failed', [
            'email' => $validated['email'] ?? null,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Login failed. Please try again.',
            'data' => null,
            'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], 500);
    }
}


    public function me(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Authenticated user fetched successfully.',
            'data' => [
                'user' => $user,
            ],
        ], 200);
    } catch (Throwable $e) {
        Log::error('Failed to fetch authenticated user', [
            'user_id' => optional($request->user())->id,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch authenticated user.',
            'data' => null,
        ], 500);
    }
}

    public function logout(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        $user->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out.',
            'data' => null,
        ], 200);

    } catch (Throwable $e) {
        Log::error('Logout failed', [
            'user_id' => optional($request->user())->id,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Logout failed. Please try again.',
            'data' => null,
            'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], 500);
    }
}

public function logoutAll(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices.',
            'data' => null,
        ], 200);

    } catch (Throwable $e) {
        Log::error('LogoutAll failed', [
            'user_id' => optional($request->user())->id,
            'exception' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Logout failed. Please try again.',
            'data' => null,
            'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], 500);
    }
}

}
