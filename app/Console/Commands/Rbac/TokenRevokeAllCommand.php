<?php

namespace App\Console\Commands\Rbac;

class TokenRevokeAllCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:token:revoke-all {user}';
    protected $description = 'Revoke all Sanctum tokens for a user';

    public function handle(): int
    {
        $this->confirmInProduction('This will revoke ALL tokens for the user. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));

        $count = $user->tokens()->count();
        $user->tokens()->delete();

        \App\Support\Audit::log('users.tokens.revoke_all', $user, [
            'revoked_count' => $count,
            'via' => 'cli',
        ]);

        $this->info("Revoked {$count} tokens for {$user->email}");
        return self::SUCCESS;
    }
}