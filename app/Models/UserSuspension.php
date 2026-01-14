<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSuspension extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'suspended_at',
        'suspended_until',
        'suspended_by',
    ];

    protected $casts = [
        'suspended_at' => 'datetime',
        'suspended_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}