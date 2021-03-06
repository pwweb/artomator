<?php

namespace PWWEB\Artomator\Commands\GraphQL;

use PWWEB\Artomator\Commands\BaseCommand;
use PWWEB\Artomator\Common\CommandData;

class GraphQLGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:graphql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD GraphQL for given model';

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

        $this->generateCommonItems();

        $this->generateGraphQLItems();

        $this->performPostActionsWithMigration();
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
