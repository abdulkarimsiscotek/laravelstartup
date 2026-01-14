<?php

namespace App\Console\Commands\Rbac;

use App\Models\User;

class UserRestoreCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:user:restore {user}';
    protected $description = 'Restore a soft-deleted user';

    public function handle(): int
    {
        $this->confirmInProduction('This will restore a previously deleted user. Continue?');

        $arg = $this->argument('user');

        $user = is_numeric($arg)
            ? User::withTrashed()->find($arg)
            : User::withTrashed()->where('email', $arg)->first();

        if (!$user) {
            $this->error("User not found (even trashed): {$arg}");
            return self::FAILURE;
        }

        if (!$user->trashed()) {
            $this->warn("User is not deleted: {$user->email}");
            return self::SUCCESS;
        }

        $user->restore();

        if (method_exists($user, 'flushRbacCache')) {
            $user->flushRbacCache();
        }

        \App\Support\Audit::log('users.restore', $user);

        $this->info("Restored {$user->email}");
        return self::SUCCESS;
    }
}