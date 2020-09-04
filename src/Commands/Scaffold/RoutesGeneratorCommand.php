<?php

namespace PWWEB\Artomator\Commands\Scaffold;

use PWWEB\Artomator\Commands\BaseCommand as Base;
use PWWEB\Artomator\Generators\Scaffold\RoutesGenerator;
use PWWEB\Artomator\Common\CommandData;
use Symfony\Component\Console\Input\InputArgument;

class RoutesGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.scaffold:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create routes command';

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

        $routesGenerator = new RoutesGenerator($this->commandData);
        $routesGenerator->regenerate();

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
        // return array_merge(parent::getArguments(), []);
        return [
            ['model', InputArgument::OPTIONAL, 'Singular Model name'],
        ];
    }
}
