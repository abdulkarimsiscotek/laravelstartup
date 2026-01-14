<?php

namespace App\Console\Commands\Rbac;

use App\Models\Role;

class UserAssignRoleCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:user:assign-role {user} {roleSlug}';
    protected $description = 'Assign a role to a user (revokes tokens)';

    public function handle(): int
    {
        $user = $this->findUserOrFail($this->argument('user'));
        $role = Role::where('slug', $this->argument('roleSlug'))->firstOrFail();

        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->flushRbacCache();
        $user->tokens()->delete();

        $this->info("Assigned role {$role->slug} to {$user->email}");
        return self::SUCCESS;
    }
}