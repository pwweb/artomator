<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use InfyOm\Generator\Commands\RollbackGeneratorCommand as Base;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\GraphQL\GraphQLInputGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLMutationGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLQueryGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLSubscriptionGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLTypeGenerator;

class RollbackGeneratorCommand extends Base
{
    /**
     * The command Data.
     *
     * @var CommandData
     */
    public $commandData;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:rollback';

    /**
     * @var Composer
     */
    public $composer;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (false === in_array($this->argument('type'), [
            CommandData::$COMMAND_TYPE_API,
            CommandData::$COMMAND_TYPE_SCAFFOLD,
            CommandData::$COMMAND_TYPE_API_SCAFFOLD,
            CommandData::$COMMAND_TYPE_GRAPHQL,
            CommandData::$COMMAND_TYPE_GRAPHQL_SCAFFOLD,
        ])) {
            $this->error('invalid rollback type');
        }

        $this->commandData = new CommandData($this, $this->argument('type'));
        $this->commandData->config->mName = $this->commandData->modelName = $this->argument('model');

        $this->commandData->config->init($this->commandData, ['tableName', 'prefix', 'plural', 'views']);

        $views = $this->commandData->getOption('views');
        if (false === empty($views)) {
            $views = explode(',', $views);
            $viewGenerator = new ViewGenerator($this->commandData);
            $viewGenerator->rollback($views);

            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();

            return;
        }

        $typeGenerator = new GraphQLTypeGenerator($this->commandData);
        $typeGenerator->rollback();

        $queryGenerator = new GraphQLQueryGenerator($this->commandData);
        $queryGenerator->rollback();

        $inputGenerator = new GraphQLInputGenerator($this->commandData);
        $inputGenerator->rollback();

        $mutationGenerator = new GraphQLMutationGenerator($this->commandData);
        $mutationGenerator->rollback();

        if (config('pwweb.artomator.options.subscription')) {
            $subscriptionGenerator = new GraphQLSubscriptionGenerator($this->commandData);
            $subscriptionGenerator->rollback();
        }

        parent::handle();
    }
}
