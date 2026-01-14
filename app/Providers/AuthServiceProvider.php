<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Map any unknown ability to privilege slug checks.
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasPrivilege') && $user->hasPrivilege($ability)) {
                return true;
            }

            return null; // allow normal policies to run if you add them later
        });
    }
}