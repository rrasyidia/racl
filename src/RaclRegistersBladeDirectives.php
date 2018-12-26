<?php

namespace Racl;

use Illuminate\Support\Facades\Blade;

/**
 * This class is the one in charge of registering
 * the blade directives making a difference
 * between the version 5.2 and 5.3
 */
class RaclRegistersBladeDirectives
{
    /**
     * Handles the registration of the blades directives.
     *
     * @param  string  $laravelVersion
     * @return void
     */
    public function handle($laravelVersion = '5.3.0')
    {
        if (version_compare(strtolower($laravelVersion), '5.3.0-dev', '>=')) {
            $this->registerWithParenthesis();
        } else {
            $this->registerWithoutParenthesis();
        }

        $this->registerClosingDirectives();
    }

    /**
     * Registers the directives with parenthesis.
     *
     * @return void
     */
    protected function registerWithParenthesis()
    {
        // Call to Racl::hasRole.
        Blade::directive('role', function ($expression) {
            return "<?php if (app('racl')->hasRole({$expression})) : ?>";
        });

        // Call to Racl::can.
        Blade::directive('permission', function ($expression) {
            return "<?php if (app('racl')->can({$expression})) : ?>";
        });

        // Call to Racl::ability.
        Blade::directive('ability', function ($expression) {
            return "<?php if (app('racl')->ability({$expression})) : ?>";
        });

        // Call to Racl::canAndOwns.
        Blade::directive('canAndOwns', function ($expression) {
            return "<?php if (app('racl')->canAndOwns({$expression})) : ?>";
        });

        // Call to Racl::hasRoleAndOwns.
        Blade::directive('hasRoleAndOwns', function ($expression) {
            return "<?php if (app('racl')->hasRoleAndOwns({$expression})) : ?>";
        });
    }

    /**
     * Registers the directives without parenthesis.
     *
     * @return void
     */
    protected function registerWithoutParenthesis()
    {
        // Call to Racl::hasRole.
        Blade::directive('role', function ($expression) {
            return "<?php if (app('racl')->hasRole{$expression}) : ?>";
        });

        // Call to Racl::can.
        Blade::directive('permission', function ($expression) {
            return "<?php if (app('racl')->can{$expression}) : ?>";
        });

        // Call to Racl::ability.
        Blade::directive('ability', function ($expression) {
            return "<?php if (app('racl')->ability{$expression}) : ?>";
        });

        // Call to Racl::canAndOwns.
        Blade::directive('canAndOwns', function ($expression) {
            return "<?php if (app('racl')->canAndOwns{$expression}) : ?>";
        });

        // Call to Racl::hasRoleAndOwns.
        Blade::directive('hasRoleAndOwns', function ($expression) {
            return "<?php if (app('racl')->hasRoleAndOwns{$expression}) : ?>";
        });
    }

    /**
     * Registers the closing directives.
     *
     * @return void
     */
    protected function registerClosingDirectives()
    {
        Blade::directive('endrole', function () {
            return "<?php endif; // app('racl')->hasRole ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; // app('racl')->can ?>";
        });

        Blade::directive('endability', function () {
            return "<?php endif; // app('racl')->ability ?>";
        });

        Blade::directive('endOwns', function () {
            return "<?php endif; // app('racl')->hasRoleAndOwns or canAndOwns ?>";
        });
    }
}
