<?php

namespace App\Console\Commands\Rbac;

class TokenRevokeCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:token:revoke {user} {tokenId}';
    protected $description = 'Revoke a single Sanctum token by ID';

    public function handle(): int
    {
        $this->confirmInProduction('This will revoke a token. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));
        $tokenId = (int) $this->argument('tokenId');

        $deleted = $user->tokens()->where('id', $tokenId)->delete();

        \App\Support\Audit::log('users.tokens.revoke', $user, [
            'token_id' => $tokenId,
            'deleted' => (bool) $deleted,
            'via' => 'cli',
        ]);

        $this->info($deleted ? "Revoked token {$tokenId}" : "Token {$tokenId} not found for user");
        return self::SUCCESS;
    }
}