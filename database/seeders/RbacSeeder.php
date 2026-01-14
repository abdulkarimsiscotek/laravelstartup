<?php

namespace Database\Seeders;

use App\Models\Privilege;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Roles
        $roles = [
            ['slug' => 'super-admin', 'name' => 'Super Admin', 'is_protected' => true],
            ['slug' => 'admin', 'name' => 'Admin', 'is_protected' => true],
            ['slug' => 'manager', 'name' => 'Manager', 'is_protected' => false],
            ['slug' => 'accountant', 'name' => 'Accountant', 'is_protected' => false],
            ['slug' => 'maintenance', 'name' => 'Maintenance', 'is_protected' => false],
            ['slug' => 'leasing-agent', 'name' => 'Leasing Agent', 'is_protected' => false],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(
                ['slug' => $r['slug']],
                ['name' => $r['name'], 'is_protected' => $r['is_protected']]
            );
        }

        // 2) Privileges (start minimal; expand as your modules grow)
        $privileges = [
            ['slug' => 'users.read', 'name' => 'Users Read'],
            ['slug' => 'users.write', 'name' => 'Users Write'],
            ['slug' => 'roles.manage', 'name' => 'Roles Manage'],
            ['slug' => 'privileges.manage', 'name' => 'Privileges Manage'],
            ['slug' => 'suspensions.manage', 'name' => 'Suspensions Manage'],
            ['slug' => 'tokens.manage', 'name' => 'Tokens Manage'],
        ];

        foreach ($privileges as $p) {
            Privilege::firstOrCreate(
                ['slug' => $p['slug']],
                ['name' => $p['name']]
            );
        }

        // 3) Attach high-level privileges to protected roles
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $admin = Role::where('slug', 'admin')->first();

        $allPrivIds = Privilege::pluck('id')->all();

        if ($superAdmin) {
            $superAdmin->privileges()->syncWithoutDetaching($allPrivIds);
        }

        if ($admin) {
            // Admin gets most privileges except anything you want reserved
            $admin->privileges()->syncWithoutDetaching($allPrivIds);
        }

        // 4) Bootstrap super-admin user if missing
        $email = env('RBAC_BOOTSTRAP_EMAIL', 'admin@example.com');
        $password = env('RBAC_BOOTSTRAP_PASSWORD', 'password');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
            ]
        );

        if ($superAdmin) {
            $user->roles()->syncWithoutDetaching([$superAdmin->id]);
        }

        if (method_exists($user, 'flushRbacCache')) {
            $user->flushRbacCache();
        }
    }
}