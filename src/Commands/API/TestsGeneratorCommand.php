<?php

namespace PWWEB\Artomator\Commands\API;

use InfyOm\Generator\Generators\API\APITestGenerator;
use InfyOm\Generator\Generators\RepositoryTestGenerator;
use PWWEB\Artomator\Commands\BaseCommand;
use PWWEB\Artomator\Common\CommandData;

class TestsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.api:tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tests command';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        $repositoryTestGenerator = new RepositoryTestGenerator($this->commandData);
        $repositoryTestGenerator->generate();

        $apiTestGenerator = new APITestGenerator($this->commandData);
        $apiTestGenerator->generate();

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
