<?php

namespace App\Console\Commands\Rbac;

use App\Auth\Services\SuspensionService;

class UserSuspendCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:user:suspend {user} {--reason=} {--until=}';
    protected $description = 'Suspend a user (revokes tokens)';

    public function handle(SuspensionService $service): int
    {
        $this->confirmInProduction('Suspending users in production affects access. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));
        $reason = $this->option('reason');

        $until = $this->option('until') ? new \DateTimeImmutable($this->option('until')) : null;

        $service->suspend($user, $reason, $until, null);

        $this->info("Suspended {$user->email}");
        return self::SUCCESS;
    }
}