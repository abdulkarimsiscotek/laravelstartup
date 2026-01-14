<?php

namespace App\Providers;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Role;
use App\Models\Privilege;
use App\Policies\RolePolicy;
use App\Policies\PrivilegePolicy;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
    $email = (string) $request->input('email');
    $ip = (string) $request->ip();

    return [
        // 10 attempts per minute per IP+email
        Limit::perMinute(10)->by($ip . '|' . strtolower($email)),
    ];
});

RateLimiter::for('rbac-admin-write', function (Request $request) {
    $userId = optional($request->user())->id ?: 'guest';
    $ip = (string) $request->ip();

    return [
        // Admin writes: 60/min per user+ip
        Limit::perMinute(60)->by($userId . '|' . $ip),
    ];
});
Gate::policy(User::class, UserPolicy::class);
Gate::policy(Role::class, RolePolicy::class);
Gate::policy(Privilege::class, PrivilegePolicy::class);

// Suspension gate: only admin/super-admin or explicit privilege
Gate::define('suspensions.manage', function (User $user): bool {
    return $user->hasAnyRole(['admin', 'super-admin']) || $user->hasPrivilege('suspensions.manage');
});

    }
}