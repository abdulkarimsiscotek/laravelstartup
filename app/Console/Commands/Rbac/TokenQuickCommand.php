<?php

namespace App\Console\Commands\Rbac;

use App\Auth\Services\TokenAbilityService;

class TokenQuickCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:token:quick {user} {--name=cli} {--revoke-old=0}';
    protected $description = 'Create a Sanctum token for a user with role+privilege abilities (prints token)';

    public function handle(TokenAbilityService $abilityService): int
    {
        $this->confirmInProduction('This will print an access token to your terminal. Continue?');

        $user = $this->findUserOrFail($this->argument('user'));

        if ($user->isSuspended()) {
            $this->error("User is suspended: {$user->email}");
            return self::FAILURE;
        }

        if ((bool) $this->option('revoke-old')) {
            $user->tokens()->delete();
        }

        $abilities = $abilityService->abilitiesFor($user);
        $tokenName = (string) $this->option('name');

        $token = $user->createToken($tokenName, $abilities);

        $this->line($token->plainTextToken); // output only token for easy copy/CI usage
        return self::SUCCESS;
    }
}