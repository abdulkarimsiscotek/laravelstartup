<?php

namespace App\Auth\Services;

use App\Models\User;
use App\Models\UserSuspension;

class SuspensionService
{
    /**
     * Suspend user. Revokes all Sanctum tokens immediately.
     */
    public function suspend(
        User $user,
        ?string $reason = null,
        ?\DateTimeInterface $until = null,
        ?int $suspendedByUserId = null
    ): UserSuspension {
        $suspension = UserSuspension::create([
            'user_id' => $user->id,
            'reason' => $reason,
            'suspended_until' => $until,
            'suspended_by' => $suspendedByUserId,
        ]);

        // Revoke all tokens immediately (incident-response friendly)
        $user->tokens()->delete();

        return $suspension;
    }

    /**
     * Remove all suspensions (simple approach).
     * Also revokes tokens to force re-login cleanly.
     */
    public function unsuspend(User $user): void
    {
        UserSuspension::where('user_id', $user->id)->delete();
        $user->tokens()->delete();
    }
}