<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // Basic, safe defaults for APIs (adjust if you serve HTML from same domain)
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // If you serve only API JSON, this is typically fine. If you also serve Blade/HTML, tailor CSP separately.
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'; base-uri 'none';");

        // HSTS only makes sense on HTTPS. Keep it conditional to avoid local dev issues.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}