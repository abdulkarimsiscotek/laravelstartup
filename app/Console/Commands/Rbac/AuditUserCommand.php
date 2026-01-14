<?php

namespace App\Console\Commands\Rbac;

use App\Models\AuditLog;

class AuditUserCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:audit:user {user} {--limit=50}';
    protected $description = 'Show audit logs related to a specific user (as target)';

    public function handle(): int
    {
        $user = $this->findUserOrFail($this->argument('user'));
        $limit = (int) $this->option('limit');

        $logs = AuditLog::query()
            ->where('target_type', get_class($user))
            ->where('target_id', $user->id)
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