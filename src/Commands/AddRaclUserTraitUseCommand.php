<?php

namespace Racl\Commands;

/**
 * This file is part of Racl,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Racl
 */

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Racl\Traits\RaclUserTrait;
use Traitor\Traitor;

class AddRaclUserTraitUseCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'racl:add-trait';

    /**
     * Trait added to User model
     *
     * @var string
     */
    protected $targetTrait = RaclUserTrait::class;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $models = $this->getUserModels() ;

        foreach ($models as $model) {
            if (!class_exists($model)) {
                $this->error("Class $model does not exist.");
                return;
            }

            if ($this->alreadyUsesRaclUserTrait($model)) {
                $this->error("Class $model already uses RaclUserTrait.");
                continue;
            }

            Traitor::addTrait($this->targetTrait)->toClass($model);
        }

        $this->info("RaclUserTrait added successfully to {$models->implode(', ')}");
    }

    /**
     * Check if the class already uses RaclUserTrait.
     *
     * @param  string  $model
     * @return bool
     */
    protected function alreadyUsesRaclUserTrait($model)
    {
        return in_array(RaclUserTrait::class, class_uses($model));
    }

    /**
     * Get the description of which clases the RaclUserTrait was added.
     *
     * @return string
     */
    public function getDescription()
    {
        return "Add RaclUserTrait to {$this->getUserModels()->implode(', ')} class";
    }

    /**
     * Return the User models array.
     *
     * @return array
     */
    protected function getUserModels()
    {
        return new Collection(Config::get('racl.user_models', []));
    }
}
