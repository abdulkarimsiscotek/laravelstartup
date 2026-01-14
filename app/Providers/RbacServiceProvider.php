<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::if('hasrole', function (string $role): bool {
            return auth()->check()
                && method_exists(auth()->user(), 'hasRole')
                && auth()->user()->hasRole($role);
        });

        Blade::if('hasanyrole', function (...$roles): bool {
            return auth()->check()
                && method_exists(auth()->user(), 'hasAnyRole')
                && auth()->user()->hasAnyRole($roles);
        });

        Blade::if('hasprivilege', function (string $privilege): bool {
            return auth()->check()
                && method_exists(auth()->user(), 'hasPrivilege')
                && auth()->user()->hasPrivilege($privilege);
        });

        Blade::if('hasanyprivilege', function (...$privileges): bool {
            return auth()->check()
                && method_exists(auth()->user(), 'hasAnyPrivilege')
                && auth()->user()->hasAnyPrivilege($privileges);
        });

        Blade::if('hasprivileges', function (...$privileges): bool {
            return auth()->check()
                && method_exists(auth()->user(), 'hasPrivileges')
                && auth()->user()->hasPrivileges($privileges);
        });

        // Gate-backed convenience (uses policies/gates)
        Blade::if('usercan', function (string $ability): bool {
            return auth()->check() && auth()->user()->can($ability);
        });
    }
}