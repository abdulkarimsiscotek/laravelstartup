<?php

namespace App\Console\Commands\Rbac;

use App\Models\Role;

class RolesListCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:roles:list';
    protected $description = 'List roles';

    public function handle(): int
    {
        $roles = Role::query()
            ->withCount('users')
            ->withCount('privileges')
            ->orderBy('id')
            ->get(['id','name','slug','is_protected']);

        $this->table(
            ['ID','Slug','Name','Protected','Users','Privileges'],
            $roles->map(fn($r) => [
                $r->id,
                $r->slug,
                $r->name,
                $r->is_protected ? 'yes' : 'no',
                $r->users_count,
                $r->privileges_count,
            ])->all()
        );

        return self::SUCCESS;
    }
}