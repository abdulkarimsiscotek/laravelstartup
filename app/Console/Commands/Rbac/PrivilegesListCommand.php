<?php

namespace App\Console\Commands\Rbac;

use App\Models\Privilege;

class PrivilegesListCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:privileges:list';
    protected $description = 'List privileges';

    public function handle(): int
    {
        $privs = Privilege::query()
            ->withCount('roles')
            ->orderBy('id')
            ->get(['id','name','slug']);

        $this->table(
            ['ID','Slug','Name','Roles'],
            $privs->map(fn($p) => [$p->id, $p->slug, $p->name, $p->roles_count])->all()
        );

        return self::SUCCESS;
    }
}