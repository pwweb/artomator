<?php

namespace PWWEB\Artomator\Commands\GraphQL;

use PWWEB\Artomator\Commands\BaseCommand;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\GraphQL\GraphQLQueryGenerator;

class GraphQLQueryGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.graphql:query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a GraphQL query command';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_GRAPHQL);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        $queryGenerator = new GraphQLQueryGenerator($this->commandData);
        $queryGenerator->generate();

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
