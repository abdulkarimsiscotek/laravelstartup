<?php

namespace App\Console\Commands\Rbac;

use App\Models\User;

class UsersListCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:users:list {--limit=50}';
    protected $description = 'List users with roles';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $users = User::query()
            ->with(['roles:id,slug'])
            ->orderBy('id')
            ->limit($limit)
            ->get(['id','name','email']);

        $this->table(
            ['ID','Email','Name','Roles'],
            $users->map(fn($u) => [
                $u->id,
                $u->email,
                $u->name,
                $u->roles->pluck('slug')->implode(', '),
            ])->all()
        );

        return self::SUCCESS;
    }
}