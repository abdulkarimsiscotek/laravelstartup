<?php

namespace App\Console\Commands\Rbac;

use App\Auth\Services\TokenAbilityService;

class TokenRotateUserCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:token:rotate-user {user} {--create=0} {--name=rotated}';
    protected $description = 'Revoke all tokens for a user, optionally issue a fresh token';

    public function handle(TokenAbilityService $abilityService): int
    {
        $this->confirmInProduction('This will revoke tokens for a user. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));
        $user->tokens()->delete();

        $this->info("Revoked all tokens for {$user->email}");

        if ((bool) $this->option('create')) {
            if ($user->isSuspended()) {
                $this->error("User is suspended; not issuing new token.");
                return self::FAILURE;
            }

            $abilities = $abilityService->abilitiesFor($user);
            $token = $user->createToken((string) $this->option('name'), $abilities);

            $this->line($token->plainTextToken);
        }

        return self::SUCCESS;
    }
}