<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Diagnostic: proves this middleware executed
        $response->headers->set('X-Force-Json', '1');

        return $response;
    }
}