<?php

namespace App\Console\Commands\Rbac;

use App\Models\Privilege;

class PrivilegeAddCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:privilege:add {slug} {name} {--description=}';
    protected $description = 'Create a new privilege';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $name = $this->argument('name');

        $p = Privilege::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'description' => $this->option('description')]
        );

        $this->info("Privilege ready: {$p->slug} (id={$p->id})");
        return self::SUCCESS;
    }
}