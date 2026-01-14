<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditRbac
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log writes to RBAC endpoints (lightweight)
        if (in_array($request->method(), ['POST','PUT','PATCH','DELETE'], true)) {
            $path = $request->path();

            if (str_starts_with($path, 'api/roles') ||
                str_starts_with($path, 'api/privileges') ||
                str_starts_with($path, 'api/users')) {

                $user = $request->user();

                Log::info('RBAC_AUDIT', [
                    'actor_id' => $user?->id,
                    'actor_email' => $user?->email,
                    'method' => $request->method(),
                    'path' => $path,
                    'ip' => $request->ip(),
                    'status' => $response->getStatusCode(),
                ]);
            }
        }

        return $response;
    }
}