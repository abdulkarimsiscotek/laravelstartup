<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Audit
{
    public static function log(
        string $action,
        ?object $target = null,
        array $meta = [],
        ?Request $request = null
    ): void {
        try {
            // In CLI/Tinker there is no real request; request() still exists but may not be helpful.
            $request = $request ?? (app()->runningInConsole() ? null : request());

            $targetType = $target ? get_class($target) : 'system';

            // Works for Eloquent models; safe for other objects
            $targetId = null;
            if ($target) {
                if (method_exists($target, 'getKey')) {
                    $targetId = $target->getKey();
                } elseif (property_exists($target, 'id')) {
                    $targetId = $target->id ?? null;
                }
            }

            AuditLog::create([
                // In console Auth::id() is null; that's fine.
                'actor_user_id' => Auth::id(),
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'meta' => !empty($meta) ? $meta : null,
                'ip' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (Throwable $e) {
            // Never allow audit to break business logic.
            Log::error('Audit::log failed', [
                'action' => $action,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}