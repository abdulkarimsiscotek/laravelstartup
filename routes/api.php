<?php

use Illuminate\Support\Facades\Route;


// if (!config('rbac.disable_api', false)) {
//     require __DIR__ . '/rbac/meta.php';
//     require __DIR__ . '/rbac/auth.php';
//     require __DIR__ . '/rbac/public.php';
//     require __DIR__ . '/rbac/admin.php';
// }

if (!config('rbac.disable_api', false)) {
    if (config('rbac.routes.meta', true)) {
        require __DIR__ . '/rbac/meta.php';
    }

    if (config('rbac.routes.auth', true)) {
        require __DIR__ . '/rbac/auth.php';
    }

    if (config('rbac.routes.public', true) && config('rbac.public_registration', true)) {
        require __DIR__ . '/rbac/public.php';
    }

    if (config('rbac.routes.admin', true)) {
        require __DIR__ . '/rbac/admin.php';
    }
}
