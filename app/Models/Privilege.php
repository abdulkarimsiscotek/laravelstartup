<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Privilege extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            config('rbac.tables.role_privilege', 'privilege_role'),
            'privilege_id',
            'role_id'
        )->withTimestamps();
    }
}