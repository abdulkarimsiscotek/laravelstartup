<?php

namespace App\Console\Commands\Rbac;

use Illuminate\Support\Facades\Artisan;

class SeedCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:seed {--admin=0 : Seed admin user from env vars}';
    protected $description = 'Seed default roles, privileges, and optionally an admin user';

    public function handle(): int
    {
        // Always confirm in production
        $this->confirmInProduction('This will seed RBAC defaults. Continue?');

        // If --admin is used, ensure env vars exist
        if ((bool) $this->option('admin')) {
            if (!env('RBAC_SEED_ADMIN_EMAIL') || !env('RBAC_SEED_ADMIN_PASSWORD')) {
                $this->error('Missing RBAC_SEED_ADMIN_EMAIL or RBAC_SEED_ADMIN_PASSWORD in .env');
                return self::FAILURE;
            }
        } else {
            // If not seeding admin, temporarily hide env vars so seeder won't create user
            // This avoids accidental admin creation when env vars exist.
            putenv('RBAC_SEED_ADMIN_EMAIL');
            putenv('RBAC_SEED_ADMIN_PASSWORD');
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RbacSeeder',
            '--force' => true,
        ]);

        $this->info('RBAC seeding complete.');
        return self::SUCCESS;
    }
}