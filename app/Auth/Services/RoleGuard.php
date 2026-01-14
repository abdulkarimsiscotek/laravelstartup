<?php

namespace App\Auth\Services;

use App\Models\Role;
use Illuminate\Validation\ValidationException;

class RoleGuard
{
    public function ensureNotProtected(Role $role): void
    {
        $protected = config('rbac.protected_role_slugs', []);
        if ($role->is_protected || in_array($role->slug, $protected, true)) {
            throw ValidationException::withMessages([
                'role' => ["Role '{$role->slug}' is protected and cannot be modified/deleted."],
            ]);
        }
    }

    public function setProtectedFlags(): void
    {
        // Optional helper: sync `is_protected` based on config list.
        $protected = config('rbac.protected_role_slugs', []);
        if (empty($protected)) return;

        Role::whereIn('slug', $protected)->update(['is_protected' => true]);
    }
}