<?php

namespace App\Console\Commands\Rbac;

use App\Models\Role;

class RoleAddCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:role:add {slug} {name} {--protected=0} {--description=}';
    protected $description = 'Create a new role';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $name = $this->argument('name');

        $role = Role::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $this->option('description'),
                'is_protected' => (bool) $this->option('protected'),
            ]
        );

        $this->info("Role ready: {$role->slug} (id={$role->id})");
        return self::SUCCESS;
    }
}