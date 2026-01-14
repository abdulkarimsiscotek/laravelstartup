<?php

namespace App\Policies;

use App\Models\Privilege;
use App\Models\User;

class PrivilegePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('privileges.manage');
    }

    public function view(User $user, Privilege $privilege): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('privileges.manage');
    }

    public function update(User $user, Privilege $privilege): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Privilege $privilege): bool
    {
        return $this->create($user);
    }
}