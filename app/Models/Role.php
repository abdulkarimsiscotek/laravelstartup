<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_protected'];

    protected static function booted(): void
    {
        static::saved(function (Role $role) {
            // If role slug/name updated, users' effective privileges may change
            $role->flushUsersRbacCache();
        });

        static::deleted(function (Role $role) {
            $role->flushUsersRbacCache();
        });
    }

    public function flushUsersRbacCache(): void
    {
        // Flush cache for all users in this role
        $this->users()
            ->select('users.id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    if (method_exists($user, 'flushRbacCache')) {
                        $user->flushRbacCache();
                    }
                }
            });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('rbac.tables.pivot', 'user_roles'),
            'role_id',
            'user_id'
        )->withTimestamps();
    }

    public function privileges(): BelongsToMany
    {
        return $this->belongsToMany(
            Privilege::class,
            config('rbac.tables.role_privilege', 'privilege_role'),
            'role_id',
            'privilege_id'
        )->withTimestamps();
    }
}