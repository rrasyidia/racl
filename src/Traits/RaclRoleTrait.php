<?php

namespace Racl\Traits;

/**
 * This file is part of Racl,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Racl
 */

use Racl\Helper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

trait RaclRoleTrait
{
    use RaclDynamicUserRelationsCalls;
    use RaclHasEvents;

    /**
     * Boots the role model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the role model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootRaclRoleTrait()
    {
        $flushCache = function ($role) {
            $role->flushCache();
        };

        // If the role doesn't use SoftDeletes.
        if (method_exists(static::class, 'restored')) {
            static::restored($flushCache);
        }

        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($role) {
            if (method_exists($role, 'bootSoftDeletes') && $role->forceDeleting) {
                return;
            }

            $role->permissions()->sync([]);

            foreach (array_keys(Config::get('racl.user_models')) as $key) {
                $role->$key()->sync([]);
            }
        });
    }

    /**
     * Tries to return all the cached permissions of the role.
     * If it can't bring the permissions from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cachedPermissions()
    {
        $cacheKey = 'racl_permissions_for_role_' . $this->getKey();

        if (! Config::get('racl.use_cache')) {
            return $this->permissions()->get();
        }

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->permissions()->get()->toArray();
        });
    }

    /**
     * Morph by Many relationship between the role and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('racl.user_models')[$relationship],
            'user',
            Config::get('racl.tables.role_user'),
            Config::get('racl.foreign_keys.role'),
            Config::get('racl.foreign_keys.user')
        );
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Config::get('racl.models.permission'),
            Config::get('racl.tables.permission_role'),
            Config::get('racl.foreign_keys.role'),
            Config::get('racl.foreign_keys.permission')
        );
    }

    /**
     * Checks if the role has a permission by its name.
     *
     * @param  string|array  $permission       Permission name or array of permission names.
     * @param  bool  $requireAll       All permissions in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            if (empty($permission)) {
                return true;
            }

            foreach ($permission as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll.
            return $requireAll;
        }

        foreach ($this->cachedPermissions() as $perm) {
            $perm = Helper::hidrateModel(Config::get('racl.models.permission'), $perm);

            if (str_is($permission, $perm->name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Save the inputted permissions.
     *
     * @param  mixed  $permissions
     * @return array
     */
    public function syncPermissions($permissions)
    {
        $mappedPermissions = [];

        foreach ($permissions as $permission) {
            $mappedPermissions[] = Helper::getIdFor($permission, $permission);
        }

        $changes = $this->permissions()->sync($permissions);
        $this->flushCache();
        $this->fireRaclEvent("permission.synced", [$this, $changes]);

        return $this;
    }

    /**
     * Attach permission to current role.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function attachPermission($permission)
    {
        $permission = Helper::getIdFor($permission, 'permission');

        $this->permissions()->attach($permission);
        $this->flushCache();
        $this->fireRaclEvent("permission.attached", [$this, $permission]);

        return $this;
    }

    /**
     * Detach permission from current role.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function detachPermission($permission)
    {
        $permission = Helper::getIdFor($permission, 'permission');

        $this->permissions()->detach($permission);
        $this->flushCache();
        $this->fireRaclEvent("permission.detached", [$this, $permission]);

        return $this;
    }

    /**
     * Attach multiple permissions to current role.
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function detachPermissions($permissions = null)
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }

        return $this;
    }

    /**
     * Flush the role's cache.
     *
     * @return void
     */
    public function flushCache()
    {
        Cache::forget('racl_permissions_for_role_' . $this->getKey());
    }
}
