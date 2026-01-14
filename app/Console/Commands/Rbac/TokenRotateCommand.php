<?php

namespace App\Console\Commands\Rbac;

use Illuminate\Support\Facades\DB;

class TokenRotateCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:token:rotate {user} {--name=rotated-cli}';
    protected $description = 'Revoke all tokens and issue a new token';

    public function handle(): int
    {
        $this->confirmInProduction('This will rotate tokens (revoke all and create new). Continue?');

        $user = $this->findUserOrFail($this->argument('user'));
        $name = (string) $this->option('name');

        $plainTextToken = null;

        DB::transaction(function () use ($user, $name, &$plainTextToken) {
            $user->tokens()->delete();

            if (method_exists($user, 'flushRbacCache')) {
                $user->flushRbacCache();
            }

            $abilities = [];

            $plainTextToken = $user->createToken($name, $abilities)->plainTextToken;

            \App\Support\Audit::log('users.tokens.rotate', $user, [
                'new_token_name' => $name,
                'via' => 'cli',
            ]);
        });

        $this->line($plainTextToken);
        return self::SUCCESS;
    }
}