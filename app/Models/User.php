<?php

namespace App\Models;

use App\Auth\Concerns\HasRolesAndPrivileges;
use App\Models\UserSuspension;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRolesAndPrivileges, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            config('rbac.tables.pivot', 'user_roles'),
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    public function assignRoleBySlug(string $slug): void
    {
        $role = \App\Models\Role::where('slug', $slug)->firstOrFail();
        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->flushRbacCache();
    }

    public function removeRoleBySlug(string $slug): void
    {
        $role = \App\Models\Role::where('slug', $slug)->first();
        if ($role) {
            $this->roles()->detach($role->id);
            $this->flushRbacCache();
        }
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(UserSuspension::class);
    }

    public function isSuspended(): bool
    {
        return $this->suspensions()
            ->where(function ($q) {
                $q->whereNull('suspended_until')
                    ->orWhere('suspended_until', '>', now());
            })
            ->exists();
    }

    public function activeSuspension(): ?UserSuspension
    {
        return $this->suspensions()
            ->where(function ($q) {
                $q->whereNull('suspended_until')
                    ->orWhere('suspended_until', '>', now());
            })
            ->latest('suspended_at')
            ->first();
    }
}
