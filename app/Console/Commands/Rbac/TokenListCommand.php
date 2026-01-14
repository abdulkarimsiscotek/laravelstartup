<?php

namespace App\Console\Commands\Rbac;

class TokenListCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:token:list {user}';
    protected $description = 'List Sanctum tokens for a user';

    public function handle(): int
    {
        $user = $this->findUserOrFail($this->argument('user'));

        $tokens = $user->tokens()
            ->select(['id','name','last_used_at','created_at','expires_at'])
            ->latest('id')
            ->get();

        $this->table(
            ['ID','Name','Last Used','Created','Expires'],
            $tokens->map(fn ($t) => [
                $t->id,
                $t->name,
                optional($t->last_used_at)?->toDateTimeString(),
                optional($t->created_at)?->toDateTimeString(),
                optional($t->expires_at)?->toDateTimeString(),
            ])->all()
        );

        return self::SUCCESS;
    }
}