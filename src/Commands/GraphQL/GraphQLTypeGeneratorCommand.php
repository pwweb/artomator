<?php

namespace PWWEB\Artomator\Commands\GraphQL;

use PWWEB\Artomator\Commands\BaseCommand;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\GraphQL\GraphQLTypeGenerator;

class GraphQLTypeGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.graphql:type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a GraphQL type command';

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

        $apiTypeGenerator = new GraphQLTypeGenerator($this->commandData);
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
