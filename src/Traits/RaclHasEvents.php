<?php

namespace Racl\Traits;

trait RaclHasEvents
{
    /**
     * Register an observer to the Racl events.
     *
     * @param  object|string  $class
     * @return void
     */
    public static function raclObserve($class)
    {
        $observables = [
            'roleAttached',
            'roleDetached',
            'permissionAttached',
            'permissionDetached',
            'roleSynced',
            'permissionSynced',
        ];

        $className = is_string($class) ? $class : get_class($class);

        foreach ($observables as $event) {
            if (method_exists($class, $event)) {
                static::registerRaclEvent(\Illuminate\Support\Str::snake($event, '.'), $className.'@'.$event);
            }
        }
    }

    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  array  $payload
     * @return mixed
     */
    protected function fireRaclEvent($event, array $payload)
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        return static::$dispatcher->fire(
            "racl.{$event}: ".static::class,
            $payload
        );
    }

    /**
     * Register a racl event with the dispatcher.
     *
     * @param  string  $event
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function registerRaclEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            $name = static::class;

            static::$dispatcher->listen("racl.{$event}: {$name}", $callback);
        }
    }

    /**
     * Register a role attached racl event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleAttached($callback)
    {
        static::registerRaclEvent('role.attached', $callback);
    }

    /**
     * Register a role detached racl event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleDetached($callback)
    {
        static::registerRaclEvent('role.detached', $callback);
    }

    /**
     * Register a permission attached racl event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionAttached($callback)
    {
        static::registerRaclEvent('permission.attached', $callback);
    }

    /**
     * Register a permission detached racl event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionDetached($callback)
    {
        static::registerRaclEvent('permission.detached', $callback);
    }

    /**
     * Register a role synced racl event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleSynced($callback)
    {
        static::registerRaclEvent('role.synced', $callback);
    }

    /**
     * Register a permission synced racl event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionSynced($callback)
    {
        static::registerRaclEvent('permission.synced', $callback);
    }
}
