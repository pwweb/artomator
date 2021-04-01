<?php

namespace PWWEB\Artomator\Commands;

use InfyOm\Generator\Commands\BaseCommand as Base;
use InfyOm\Generator\Generators\Scaffold\ControllerGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLInputGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLMutationGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLQueryGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLSubscriptionGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLTypeGenerator;
use PWWEB\Artomator\Generators\InterfaceGenerator;
use PWWEB\Artomator\Generators\Scaffold\RoutesGenerator;
use PWWEB\Artomator\Generators\Scaffold\ViewGenerator;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends Base
{
    public function handle()
    {
        parent::handle();
        $this->commandData->config->prepareGraphQLNames($this->option('gqlName'));
        $this->commandData = $this->commandData->config->loadDynamicGraphQLVariables($this->commandData);
    }

    public function generateCommonItems()
    {
        parent::generateCommonItems();

        if (! $this->isSkip('repository') && $this->commandData->getOption('repositoryPattern')) {
            $interfaceGenerator = new InterfaceGenerator($this->commandData);
            $interfaceGenerator->generate();
        }
    }

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

        if (false === $this->isSkip('views')) {
            $viewGenerator = new ViewGenerator($this->commandData);
            $viewGenerator->generate();
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
        return array_merge(parent::getOptions(), [
            ['gqlName', null, InputOption::VALUE_REQUIRED, 'Override the name used in the GraphQL schema file'],
        ]);
    }
}
