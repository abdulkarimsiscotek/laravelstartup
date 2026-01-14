<?php

namespace App\Console\Commands\Rbac;

use App\Models\User;
use Illuminate\Console\Command;

abstract class BaseRbacCommand extends Command
{
    protected function findUserOrFail(string $identifier): User
    {
        // supports ID or email
        $user = is_numeric($identifier)
            ? User::find($identifier)
            : User::where('email', $identifier)->first();

        if (!$user) {
            $this->error("User not found: {$identifier}");
            exit(1);
        }

        return $user;
    }

    protected function confirmInProduction(string $message = 'You are in production. Continue?'): void
    {
        if (app()->environment('production') && !$this->confirm($message)) {
            $this->warn('Aborted.');
            exit(0);
        }
    }
}