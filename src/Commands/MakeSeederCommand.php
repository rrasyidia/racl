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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class MakeSeederCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'racl:seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the seeder following the Racl specifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel->view->addNamespace('racl', substr(__DIR__, 0, -8).'views');

        if (file_exists($this->seederPath())) {
            $this->line('');

            $this->warn("The RaclSeeder file already exists. Delete the existing one if you want to create a new one.");
            $this->line('');
            return;
        }

        if ($this->createSeeder()) {
            $this->info("Seeder successfully created!");
        } else {
            $this->error(
                "Couldn't create seeder.\n".
                "Check the write permissions within the database/seeds directory."
            );
        }

        $this->line('');
    }

    /**
     * Create the seeder
     *
     * @return bool
     */
    protected function createSeeder()
    {
        $permission = Config::get('racl.models.permission', 'App\Permission');
        $role = Config::get('racl.models.role', 'App\Role');
        $rolePermissions = Config::get('racl.tables.permission_role');
        $roleUsers = Config::get('racl.tables.role_user');
        $user = new Collection(Config::get('racl.user_models', ['App\User']));
        $user = $user->first();

        $output = $this->laravel->view->make('racl::seeder')
            ->with(compact([
                'role',
                'permission',
                'user',
                'rolePermissions',
                'roleUsers',
            ]))
            ->render();

        if ($fs = fopen($this->seederPath(), 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }

    /**
     * Get the seeder path.
     *
     * @return string
     */
    protected function seederPath()
    {
        return database_path("seeds/RaclSeeder.php");
    }
}
