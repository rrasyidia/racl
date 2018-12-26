<?php

namespace Racl\Traits;

/**
 * This file is part of Racl,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Racl
 */

use Illuminate\Support\Facades\Config;
use Racl\Traits\RaclDynamicUserRelationsCalls;

trait RaclPermissionTrait
{
    use RaclDynamicUserRelationsCalls;

    /**
     * Boots the permission model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootRaclPermissionTrait()
    {
        static::deleting(function ($permission) {
            if (!method_exists(Config::get('racl.models.permission'), 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }
        });

        static::deleting(function ($permission) {
            if (method_exists($permission, 'bootSoftDeletes') && $permission->forceDeleting) {
                return;
            }

            $permission->roles()->sync([]);

            foreach (array_keys(Config::get('racl.user_models')) as $key) {
                $permission->$key()->sync([]);
            }
        });
    }

    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Config::get('racl.models.role'),
            Config::get('racl.tables.permission_role'),
            Config::get('racl.foreign_keys.permission'),
            Config::get('racl.foreign_keys.role')
        );
    }

    /**
     * Morph by Many relationship between the permission and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('racl.user_models')[$relationship],
            'user',
            Config::get('racl.tables.permission_user'),
            Config::get('racl.foreign_keys.permission'),
            Config::get('racl.foreign_keys.user')
        );
    }
}
