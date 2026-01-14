<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Users can update themselves.
     * Admin/super-admin can update anyone.
     */
    public function update(User $actor, User $target): bool
    {
        // Admins can update anyone (role OR ability)
        if ($actor->hasAnyRole(['admin', 'super-admin']) || $actor->can('users.write')) {
            return true;
        }

        // Normal users can only update themselves
        return $actor->id === $target->id;
    }
}