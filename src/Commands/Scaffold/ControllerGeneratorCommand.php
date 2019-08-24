<?php

namespace PWWEB\Artomator\Commands\Scaffold;

use PWWEB\Artomator\Commands\BaseCommand;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\Scaffold\ControllerGenerator;

class ControllerGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.scaffold:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create controller command';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_SCAFFOLD);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        $controllerGenerator = new ControllerGenerator($this->commandData);
        $controllerGenerator->generate();

        $this->performPostActions();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }
}
