<?php

namespace App\Auth\Services;

use App\Models\User;

class TokenAbilityService
{
    public function abilitiesFor(User $user): array
    {
        $roles = $user->roles()->pluck('slug')->values()->all();

        $privileges = method_exists($user, 'resolvedPrivilegeSlugs')
            ? $user->resolvedPrivilegeSlugs()
            : [];

        return array_values(array_unique(array_merge($roles, $privileges)));
    }
}