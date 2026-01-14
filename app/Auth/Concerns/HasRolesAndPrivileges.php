<?php

namespace App\Auth\Concerns;

use App\Models\Privilege;
use Illuminate\Support\Facades\Cache;

trait HasRolesAndPrivileges
{
    /**
     * NOTE:
     * - roles() relationship is defined in User model from Module 1.
     * - privileges are derived via roles -> privileges pivot.
     */

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function hasPrivilege(string $slug): bool
    {
        return $this->hasAnyPrivilege([$slug]);
    }

    public function hasAnyPrivilege(array $slugs): bool
    {
        $slugs = array_values(array_unique(array_filter($slugs)));
        if (empty($slugs)) return false;

        $userPrivilegeSlugs = $this->resolvedPrivilegeSlugs();

        foreach ($slugs as $slug) {
            if (in_array($slug, $userPrivilegeSlugs, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True only if user has ALL requested privilege slugs.
     */
    public function hasPrivileges(array $slugs): bool
    {
        $slugs = array_values(array_unique(array_filter($slugs)));
        if (empty($slugs)) return true;

        $userPrivilegeSlugs = $this->resolvedPrivilegeSlugs();

        foreach ($slugs as $slug) {
            if (!in_array($slug, $userPrivilegeSlugs, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns privilege slugs granted via the user's roles.
     * Uses cache if enabled in config/rbac.php.
     */
    public function resolvedPrivilegeSlugs(): array
    {
        $cacheEnabled = (bool) config('rbac.cache.enabled', true);
        $ttl = (int) config('rbac.cache.ttl', 300);
        $store = config('rbac.cache.store');

        $key = "rbac:user:{$this->getKey()}:privileges";

        if (!$cacheEnabled) {
            return $this->queryPrivilegeSlugs();
        }

        $cache = $store ? Cache::store($store) : Cache::store();

        return $cache->remember($key, $ttl, function () {
            return $this->queryPrivilegeSlugs();
        });
    }

    /**
     * Clear cached privileges (call after role/privilege changes).
     */
    public function flushRbacCache(): void
    {
        $cacheEnabled = (bool) config('rbac.cache.enabled', true);
        if (!$cacheEnabled) return;

        $store = config('rbac.cache.store');
        $key = "rbac:user:{$this->getKey()}:privileges";

        $cache = $store ? Cache::store($store) : Cache::store();
        $cache->forget($key);
    }

    private function queryPrivilegeSlugs(): array
    {
        // Efficient: privileges via role relationships, no N+1
        return Privilege::query()
            ->select('privileges.slug')
            ->distinct()
            ->join(config('rbac.tables.role_privilege', 'privilege_role') . ' as pr', 'pr.privilege_id', '=', 'privileges.id')
            ->join(config('rbac.tables.roles', 'roles') . ' as r', 'r.id', '=', 'pr.role_id')
            ->join(config('rbac.tables.pivot', 'user_roles') . ' as ur', 'ur.role_id', '=', 'r.id')
            ->where('ur.user_id', $this->getKey())
            ->pluck('privileges.slug')
            ->values()
            ->all();
    }
}