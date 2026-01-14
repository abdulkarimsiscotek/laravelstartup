<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            // IMPORTANT: override auth middleware to prevent redirect-to-login for API
            'auth'          => \App\Http\Middleware\Authenticate::class,

            'abilities'     => CheckAbilities::class,
            'ability'       => CheckForAnyAbility::class,

            'force_json'    => \App\Http\Middleware\ForceJsonResponse::class,
            'not_suspended' => \App\Http\Middleware\EnsureNotSuspended::class,
            'rbac_audit'    => \App\Http\Middleware\AuditRbac::class,
            'sec_headers'   => \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // IMPORTANT: Force JSON for ALL /api/* routes and run it BEFORE auth
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'data' => null,
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.',
                    'data' => null,
                ], 403);
            }
        });

        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data' => null,
                    'error' => [
                        'errors' => $e->errors(),
                    ],
                ], 422);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if ($request->expectsJson()) {
                $status = $e->getStatusCode();

                $message = $e->getMessage();
                if (!$message) {
                    $message = match ($status) {
                        404 => 'Not found.',
                        405 => 'Method not allowed.',
                        default => 'Request error.',
                    };
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null,
                ], $status);
            }
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error.',
                    'data' => null,
                    'error' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
                ], 500);
            }
        });
    })
    ->create();