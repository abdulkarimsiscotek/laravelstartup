<?php

namespace App\Console\Commands\Rbac;

use App\Models\AuditLog;

class AuditListCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:audit:list {--limit=50} {--action=}';
    protected $description = 'List audit logs';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $action = $this->option('action');

        $q = AuditLog::query()->with('actor:id,name,email')->latest('id');

        if ($action) {
            $q->where('action', $action);
        }

        $logs = $q->limit($limit)->get();

        $this->table(
            ['ID', 'At', 'Actor', 'Action', 'Target', 'Target ID'],
            $logs->map(fn ($l) => [
                $l->id,
                $l->created_at?->toDateTimeString(),
                $l->actor ? $l->actor->email : 'system',
                $l->action,
                class_basename($l->target_type),
                $l->target_id,
            ])->all()
        );

        return self::SUCCESS;
    }
}