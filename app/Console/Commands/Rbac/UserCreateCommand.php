<?php

namespace App\Console\Commands\Rbac;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCreateCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:user:create {email} {name} {password}';
    protected $description = 'Create a user and assign default role';

    public function handle(): int
    {
        $this->confirmInProduction('Creating users in production can be risky. Continue?');

        $email = $this->argument('email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $this->argument('name'),
                'password' => Hash::make($this->argument('password')),
            ]
        );

        $defaultSlug = config('rbac.default_user_role_slug', 'user');
        $defaultRole = Role::where('slug', $defaultSlug)->first();

        if ($defaultRole) {
            $user->roles()->syncWithoutDetaching([$defaultRole->id]);
            $user->flushRbacCache();
        }

        $this->info("User ready: {$user->email} (id={$user->id})");
        return self::SUCCESS;
    }
}