<?php

return [
    'version' => env('RBAC_VERSION', '1.0.0'),

    'disable_commands' => env('RBAC_DISABLE_COMMANDS', false),

    'guard' => env('RBAC_GUARD', 'sanctum'),

    'route_prefix' => env('RBAC_ROUTE_PREFIX', 'api'),
    'route_name_prefix' => env('RBAC_ROUTE_NAME_PREFIX', 'rbac.'),
    'route_middleware' => ['api'],
    'load_default_routes' => true,

    // Master switch for API exposure
    'disable_api' => env('RBAC_DISABLE_API', false),

    // Fine-grained route group toggles
    'routes' => [
        'meta' => env('RBAC_ROUTES_META', true),
        'auth' => env('RBAC_ROUTES_AUTH', true),
        'public' => env('RBAC_ROUTES_PUBLIC', true), // registration endpoint
        'admin' => env('RBAC_ROUTES_ADMIN', true),
    ],

    // Disable public registration in production by env
    'public_registration' => env('RBAC_PUBLIC_REGISTRATION', true),

    'tables' => [
        'users' => env('RBAC_USERS_TABLE', 'users'),
        'roles' => env('RBAC_ROLES_TABLE', 'roles'),
        'pivot' => env('RBAC_USER_ROLES_TABLE', 'user_roles'),
        'privileges' => env('RBAC_PRIVILEGES_TABLE', 'privileges'),
        'role_privilege' => env('RBAC_ROLE_PRIVILEGE_TABLE', 'privilege_role'),
    ],

    'default_user_role_slug' => env('RBAC_DEFAULT_ROLE_SLUG', 'user'),

    'protected_role_slugs' => ['admin', 'super-admin'],

    'delete_previous_access_tokens_on_login' => env('RBAC_DELETE_PREVIOUS_TOKENS_ON_LOGIN', false),

    'cache' => [
        'enabled' => env('RBAC_CACHE_ENABLED', true),
        'store' => env('RBAC_CACHE_STORE', null),
        'ttl' => env('RBAC_CACHE_TTL', 300),
    ],

    'password' => [
        'min_length' => env('RBAC_PASSWORD_MIN_LENGTH', 8),
        'max_length' => env('RBAC_PASSWORD_MAX_LENGTH', null),

        'require_confirmation' => env('RBAC_PASSWORD_REQUIRE_CONFIRMATION', false),

        'complexity' => [
            'require_uppercase' => env('RBAC_PASSWORD_REQUIRE_UPPERCASE', false),
            'require_lowercase' => env('RBAC_PASSWORD_REQUIRE_LOWERCASE', false),
            'require_numbers' => env('RBAC_PASSWORD_REQUIRE_NUMBERS', false),
            'require_special_chars' => env('RBAC_PASSWORD_REQUIRE_SPECIAL_CHARS', false),
        ],

        'check_common_passwords' => env('RBAC_PASSWORD_CHECK_COMMON', false),
        'disallow_user_info' => env('RBAC_PASSWORD_DISALLOW_USER_INFO', false),
    ],

    'abilities' => [
        'admin' => ['admin', 'super-admin'],
        'user_update' => ['admin', 'super-admin', 'user'],
    ],
];