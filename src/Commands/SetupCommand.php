<?php

namespace Racl\Commands;

/**
 * This file is part of Racl,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Racl
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'racl:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup migration and models for Racl';

    /**
     * Commands to call with their description.
     *
     * @var array
     */
    protected $calls = [
        'racl:migration' => 'Creating migration',
        'racl:role' => 'Creating Role model',
        'racl:permission' => 'Creating Permission model',
        'racl:add-trait' => 'Adding RaclUserTrait to User model'
    ];

    /**
     * Create a new command instance
     *
     * @return void
     */
    public function __construct()
    {
        if (Config::get('racl.use_teams')) {
            $this->calls['racl:team'] = 'Creating Team model';
        }

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->calls as $command => $info) {
            $this->line(PHP_EOL . $info);
            $this->call($command);
        }
    }
}
