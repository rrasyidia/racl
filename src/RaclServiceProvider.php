<?php

namespace Racl;

/**
 * This file is part of Racl,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Racl
 */

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class RaclServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'Migration' => 'command.racl.migration',
        'MakeRole' => 'command.racl.role',
        'MakePermission' => 'command.racl.permission',
        'MakeTeam' => 'command.racl.team',
        'AddRaclUserTraitUse' => 'command.racl.add-trait',
        'Setup' => 'command.racl.setup',
        'SetupTeams' => 'command.racl.setup-teams',
        'MakeSeeder' => 'command.racl.seeder',
        'Upgrade' => 'command.racl.upgrade'
    ];

    /**
     * The middlewares to be registered.
     *
     * @var array
     */
    protected $middlewares = [
        'role' => \Racl\Middleware\RaclRole::class,
        'permission' => \Racl\Middleware\RaclPermission::class,
        'ability' => \Racl\Middleware\RaclAbility::class,
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Register published configuration.
        $this->publishes([
            __DIR__.'/config/racl.php' => config_path('racl.php'),
            __DIR__.'/config/racl_seeder.php' => config_path('racl_seeder.php'),
        ], 'racl');

        $this->useMorphMapForRelationships();

        $this->autoRegisterMiddlewares();

        if (class_exists('\Blade')) {
            $this->registerBladeDirectives();
        }
    }

    /**
     * If the user wants to use the morphMap it uses the morphMap.
     *
     * @return void
     */
    protected function useMorphMapForRelationships()
    {
        if ($this->app['config']->get('racl.use_morph_map')) {
            Relation::morphMap($this->app['config']->get('racl.user_models'));
        }
    }

    /**
     * Register the middlewares automatically.
     *
     * @return void
     */
    protected function autoRegisterMiddlewares()
    {
        if (!$this->app['config']->get('racl.middleware.register')) {
            return;
        }

        $router = $this->app['router'];

        if (method_exists($router, 'middleware')) {
            $registerMethod = 'middleware';
        } elseif (method_exists($router, 'aliasMiddleware')) {
            $registerMethod = 'aliasMiddleware';
        } else {
            return;
        }

        foreach ($this->middlewares as $key => $class) {
            $router->$registerMethod($key, $class);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRacl();

        $this->registerCommands();

        $this->mergeConfig();
    }

    /**
     * Register the blade directives.
     *
     * @return void
     */
    private function registerBladeDirectives()
    {
        (new RaclRegistersBladeDirectives)->handle($this->app->version());
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerRacl()
    {
        $this->app->bind('racl', function ($app) {
            return new Racl($app);
        });

        $this->app->alias('racl', 'Racl\Racl');
    }

    /**
     * Register the given commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        foreach (array_keys($this->commands) as $command) {
            $method = "register{$command}Command";

            call_user_func_array([$this, $method], []);
        }

        $this->commands(array_values($this->commands));
    }

    protected function registerMigrationCommand()
    {
        $this->app->singleton('command.racl.migration', function () {
            return new \Racl\Commands\MigrationCommand();
        });
    }

    protected function registerMakeRoleCommand()
    {
        $this->app->singleton('command.racl.role', function ($app) {
            return new \Racl\Commands\MakeRoleCommand($app['files']);
        });
    }

    protected function registerMakePermissionCommand()
    {
        $this->app->singleton('command.racl.permission', function ($app) {
            return new \Racl\Commands\MakePermissionCommand($app['files']);
        });
    }

    protected function registerMakeTeamCommand()
    {
        $this->app->singleton('command.racl.team', function ($app) {
            return new \Racl\Commands\MakeTeamCommand($app['files']);
        });
    }

    protected function registerAddRaclUserTraitUseCommand()
    {
        $this->app->singleton('command.racl.add-trait', function () {
            return new \Racl\Commands\AddRaclUserTraitUseCommand();
        });
    }

    protected function registerSetupCommand()
    {
        $this->app->singleton('command.racl.setup', function () {
            return new \Racl\Commands\SetupCommand();
        });
    }

    protected function registerSetupTeamsCommand()
    {
        $this->app->singleton('command.racl.setup-teams', function () {
            return new \Racl\Commands\SetupTeamsCommand();
        });
    }

    protected function registerMakeSeederCommand()
    {
        $this->app->singleton('command.racl.seeder', function () {
            return new \Racl\Commands\MakeSeederCommand();
        });
    }

    protected function registerUpgradeCommand()
    {
        $this->app->singleton('command.racl.upgrade', function () {
            return new \Racl\Commands\UpgradeCommand();
        });
    }

    /**
     * Merges user's and racl's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/racl.php',
            'racl'
        );
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }
}
