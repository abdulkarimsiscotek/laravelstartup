<?php

namespace App\Console\Commands\Rbac;

use App\Auth\Services\SuspensionService;

class UserUnsuspendCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:user:unsuspend {user}';
    protected $description = 'Unsuspend a user (also revokes tokens to force clean re-login)';

    public function handle(SuspensionService $service): int
    {
        $this->confirmInProduction('Unsuspending a user restores access. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));

        $service->unsuspend($user);

        $this->info("Unsuspended {$user->email}");
        return self::SUCCESS;
    }
}