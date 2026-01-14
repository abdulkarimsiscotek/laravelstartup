<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    /**
     * Ensure /api/* never redirects to route('login')
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            throw new AuthenticationException('Unauthenticated.', $guards);
        }

        parent::unauthenticated($request, $guards);
    }
}