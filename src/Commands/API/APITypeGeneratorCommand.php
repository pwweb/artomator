<?php

namespace PWWEB\Artomator\Commands\API;

use PWWEB\Artomator\Commands\BaseCommand;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\API\APITypeGenerator;

class APITypeGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.api:type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create type command';

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

        $apiTypeGenerator = new APITypeGenerator($this->commandData);
        $apiTypeGenerator->generate();

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
