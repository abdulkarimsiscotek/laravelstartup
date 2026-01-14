<?php

namespace App\Console\Commands\Rbac;

class UserLogoutAllCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:user:logout-all {user}';
    protected $description = 'Revoke all tokens for a user';

    public function handle(): int
    {
        $this->confirmInProduction('This will log the user out everywhere. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));
        $count = $user->tokens()->count();
        $user->tokens()->delete();

        $this->info("Revoked {$count} tokens for {$user->email}");
        return self::SUCCESS;
    }
}