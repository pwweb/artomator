<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use InfyOm\Generator\Commands\BaseCommand as Base;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use InfyOm\Generator\Utils\FileUtil;
use PWWEB\Artomator\Generators\ContractGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLInputGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLMutationGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLQueryGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLSubscriptionGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLTypeGenerator;
use PWWEB\Artomator\Generators\Scaffold\ControllerGenerator;
use PWWEB\Artomator\Generators\Scaffold\RoutesGenerator;
use PWWEB\Artomator\Generators\Scaffold\ViewGenerator;
use PWWEB\Artomator\Generators\Scaffold\VueGenerator;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends Base
{
    /**
     * Handle.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
        $this->commandData->config->prepareGraphQLNames($this->option('gqlName'));
        $this->commandData = $this->commandData->config->loadDynamicGraphQLVariables($this->commandData);
    }

    /**
     * Generate Common Items.
     *
     * @return void
     */
    public function generateCommonItems()
    {
        parent::generateCommonItems();

        if (false === $this->isSkip('repository') && true === $this->commandData->getOption('repositoryPattern')) {
            $contractGenerator = new ContractGenerator($this->commandData);
            $contractGenerator->generate();
        }
    }

    /**
     * Generate GraphQL Items.
     *
     * @return void
     */
    public function generateGraphQLItems()
    {
        if (false === ($this->isSkip('queries') or $this->isSkip('graphql_query'))) {
            $queryGenerator = new GraphQLQueryGenerator($this->commandData);
            $queryGenerator->generate();
        }

        if (false === ($this->isSkip('types') or $this->isSkip('graphql_types'))) {
            $typeGenerator = new GraphQLTypeGenerator($this->commandData);
            $typeGenerator->generate();
        }

        if (false === ($this->isSkip('mutations') or $this->isSkip('graphql_mutations'))) {
            $mutationGenerator = new GraphQLMutationGenerator($this->commandData);
            $mutationGenerator->generate();
        }

        if (false === ($this->isSkip('inputs') or $this->isSkip('graphql_inputs'))) {
            $mutationGenerator = new GraphQLInputGenerator($this->commandData);
            $mutationGenerator->generate();
        }

        if ((false === ($this->isSkip('subscription') or $this->isSkip('graphql_subscription'))) and config('pwweb.artomator.options.subscription')) {
            $subscriptionGenerator = new GraphQLSubscriptionGenerator($this->commandData);
            $subscriptionGenerator->generate();
        }
    }

    /**
     * Generate Scaffold Items.
     *
     * @return void
     */
    public function generateScaffoldItems()
    {
        if (false === $this->isSkip('requests') and false === $this->isSkip('scaffold_requests')) {
            $requestGenerator = new RequestGenerator($this->commandData);
            $requestGenerator->generate();
        }

        if (false === $this->isSkip('controllers') and false === $this->isSkip('scaffold_controller')) {
            $controllerGenerator = new ControllerGenerator($this->commandData);
            $controllerGenerator->generate();
        }

        if (false === $this->isSkip('views') and false === $this->commandData->getOption('vue')) {
            $viewGenerator = new ViewGenerator($this->commandData);
            $viewGenerator->generate();
        }

        if (false === $this->isSkip('views') and true === $this->commandData->getOption('vue')) {
            $vueGenerator = new VueGenerator($this->commandData);
            $vueGenerator->generate();
        }

        if (false === $this->isSkip('routes') and false === $this->isSkip('scaffold_routes')) {
            $routeGenerator = new RoutesGenerator($this->commandData);
            $routeGenerator->generate();
        }

        if (false === $this->isSkip('menu') and $this->commandData->config->getAddOn('menu.enabled')) {
            $menuGenerator = new MenuGenerator($this->commandData);
            $menuGenerator->generate();
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['gqlName', null, InputOption::VALUE_REQUIRED, 'Override the name used in the GraphQL schema file'],
                ['vue', null, InputOption::VALUE_NONE, 'Generate Vuejs views rather than blade views'],
            ]
        );
    }

    /**
     * Perform the Post Generator Actions.
     *
     * @param bool $runMigration Boolean flag to run migrations.
     *
     * @return void
     */
    public function performPostActions($runMigration = false)
    {
        if (true === $this->commandData->getOption('save')) {
            $this->saveSchemaFile();
        }

        if (true === $runMigration) {
            if (true === $this->commandData->getOption('forceMigrate')) {
                $this->runMigration();
            } elseif (false === $this->commandData->getOption('fromTable') && false === $this->isSkip('migration')) {
                $requestFromConsole = ('cli' === php_sapi_name()) ? true : false;
                if (true === $this->commandData->getOption('jsonFromGUI') && true === $requestFromConsole) {
                    $this->runMigration();
                } elseif (true === $requestFromConsole && true === $this->confirm("\nDo you want to migrate database? [y|N]", false)) {
                    $this->runMigration();
                }
            }
        }

        if (true === $this->commandData->getOption('localized')) {
            $this->saveLocaleFile();
        }

        if (false === $this->isSkip('dump-autoload')) {
            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
        }
    }

    /**
     * Save the Schema File.
     *
     * @return void
     */
    private function saveSchemaFile()
    {
        $fileFields = [];

        foreach ($this->commandData->fields as $field) {
            $fileFields[] = [
                'name'        => $field->name,
                'dbType'      => $field->dbInput,
                'htmlType'    => $field->htmlInput,
                'validations' => $field->validations,
                'searchable'  => $field->isSearchable,
                'fillable'    => $field->isFillable,
                'primary'     => $field->isPrimary,
                'inForm'      => $field->inForm,
                'inIndex'     => $field->inIndex,
                'inView'      => $field->inView,
            ];
        }

        foreach ($this->commandData->relations as $relation) {
            $fileFields[] = [
                'type'     => 'relation',
                'relation' => $relation->type.','.implode(',', $relation->inputs),
            ];
        }

        $path = $this->commandData->config->pathSchemas;

        $fileName = $this->commandData->modelName.'.json';

        if (true === file_exists($path.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }
        FileUtil::createFile($path, $fileName, json_encode($fileFields, JSON_PRETTY_PRINT));
        $this->commandData->commandComment("\nSchema File saved: ");
        $this->commandData->commandInfo($fileName);
    }

    /**
     * Save the Locale File.
     *
     * @return void
     */
    private function saveLocaleFile()
    {
        $locales = [
            'singular' => $this->commandData->modelName,
            'plural'   => $this->commandData->config->mPlural,
            'fields'   => [],
        ];

        foreach ($this->commandData->fields as $field) {
            $locales['fields'][$field->name] = Str::title(str_replace('_', ' ', $field->name));
        }

        $path = $this->commandData->config->pathLocales;

        $fileName = $this->commandData->config->mCamelPlural.'.php';

        if (true === file_exists($path.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }
        $content = "<?php\n\nreturn ".var_export($locales, true).';'.\PHP_EOL;
        FileUtil::createFile($path, $fileName, $content);
        $this->commandData->commandComment("\nModel Locale File saved: ");
        $this->commandData->commandInfo($fileName);
    }
}
