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

trait RaclTeamTrait
{
    use RaclDynamicUserRelationsCalls;

    /**
     * Morph by Many relationship between the role and the one of the possible user models.
     *
     * @param  string $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('racl.user_models')[$relationship],
            'user',
            Config::get('racl.tables.role_user'),
            Config::get('racl.foreign_keys.team'),
            Config::get('racl.foreign_keys.user')
        );
    }

    /**
     * Boots the team model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the team model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootRaclTeamTrait()
    {
        static::deleting(function ($team) {
            if (method_exists($team, 'bootSoftDeletes') && $team->forceDeleting) {
                return;
            }

            foreach (array_keys(Config::get('racl.user_models')) as $key) {
                $team->$key()->sync([]);
            }
        });
    }
}
