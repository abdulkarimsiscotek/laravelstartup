<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('roles.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        if (!$this->create($user)) {
            return false;
        }

        // Prevent non-super-admin from modifying protected roles
        if ($role->is_protected || in_array($role->slug, config('rbac.protected_role_slugs', ['admin', 'super-admin']), true)) {
            return $user->hasRole('super-admin');
        }

        return true;
    }

    public function delete(User $user, Role $role): bool
    {
        // Same rules as update
        return $this->update($user, $role);
    }
}