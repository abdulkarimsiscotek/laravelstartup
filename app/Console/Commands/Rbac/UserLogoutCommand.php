<?php

namespace App\Console\Commands\Rbac;

class UserLogoutCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:user:logout {user}';
    protected $description = 'Revoke the most recently created token for a user (best effort)';

    public function handle(): int
    {
        $this->confirmInProduction('This will revoke one token. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));

        $token = $user->tokens()->latest('id')->first();

        if (!$token) {
            $this->warn("No tokens found for {$user->email}");
            return self::SUCCESS;
        }

        $token->delete();
        $this->info("Revoked latest token for {$user->email} (token_id={$token->id})");
        return self::SUCCESS;
    }
}