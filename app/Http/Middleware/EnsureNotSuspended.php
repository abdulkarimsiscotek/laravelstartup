<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isSuspended') && $user->isSuspended()) {
            $active = method_exists($user, 'activeSuspension') ? $user->activeSuspension() : null;

            return response()->json([
                'message' => 'Account suspended.',
                'reason' => $active?->reason,
                'suspended_until' => optional($active?->suspended_until)->toISOString(),
            ], 403);
        }

        return $next($request);
    }
}