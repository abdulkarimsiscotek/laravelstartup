<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RbacRolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Default role for newly registered users.',
                'is_protected' => false,
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Administrative role.',
                'is_protected' => true,
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Highest privilege role.',
                'is_protected' => true,
            ]
        );
    }
}