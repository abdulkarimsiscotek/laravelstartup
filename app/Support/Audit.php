<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Audit
{
    /**
     * Lightweight audit logger.
     * You can later swap this implementation to write to an audits table.
     */
    public static function log(string $event, ?Model $model = null, array $meta = []): void
    {
        $user = Auth::user();

        Log::info('AUDIT', [
            'event' => $event,
            'actor_id' => $user?->getAuthIdentifier(),
            'actor_email' => $user?->email,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'meta' => $meta,
        ]);
    }
}