<?php

namespace App\Console\Commands\Rbac;

use App\Models\Privilege;
use App\Models\Role;

class RoleAttachPrivilegeCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:role:attach-privilege {roleSlug} {privilegeSlug}';
    protected $description = 'Attach a privilege to a role';

    public function handle(): int
    {
        $role = Role::where('slug', $this->argument('roleSlug'))->firstOrFail();
        $priv = Privilege::where('slug', $this->argument('privilegeSlug'))->firstOrFail();

        $role->privileges()->syncWithoutDetaching([$priv->id]);

        if (method_exists($role, 'flushUsersRbacCache')) {
            $role->flushUsersRbacCache();
        }

        $this->info("Attached {$priv->slug} -> {$role->slug}");
        return self::SUCCESS;
    }
}