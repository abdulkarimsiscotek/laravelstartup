<?php

namespace App\Console\Commands\Rbac;

use App\Models\AuditLog;
use App\Models\Role;

class AuditRoleCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:audit:role {role} {--limit=50}';
    protected $description = 'Show audit logs related to a specific role (as target)';

    public function handle(): int
    {
        $arg = $this->argument('role');

        $role = is_numeric($arg)
            ? Role::find($arg)
            : Role::where('slug', $arg)->first();

        if (!$role) {
            $this->error("Role not found: {$arg}");
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');

        $logs = AuditLog::query()
            ->where('target_type', get_class($role))
            ->where('target_id', $role->id)
            ->with('actor:id,name,email')
            ->latest('id')
            ->limit($limit)
            ->get();

        $this->table(
            ['ID', 'At', 'Actor', 'Action', 'Meta'],
            $logs->map(fn ($l) => [
                $l->id,
                $l->created_at?->toDateTimeString(),
                $l->actor ? $l->actor->email : 'system',
                $l->action,
                $l->meta ? json_encode($l->meta) : '',
            ])->all()
        );

        return self::SUCCESS;
    }
}