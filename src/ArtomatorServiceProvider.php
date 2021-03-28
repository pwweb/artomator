<?php

namespace PWWEB\Artomator;

use InfyOm\Generator\InfyOmGeneratorServiceProvider as ServiceProvider;
use PWWEB\Artomator\Commands\API\APIControllerGeneratorCommand;
use PWWEB\Artomator\Commands\API\APIGeneratorCommand;
use PWWEB\Artomator\Commands\API\APIRequestsGeneratorCommand;
use PWWEB\Artomator\Commands\API\TestsGeneratorCommand;
use PWWEB\Artomator\Commands\APIScaffoldGeneratorCommand;
use PWWEB\Artomator\Commands\Common\MigrationGeneratorCommand;
use PWWEB\Artomator\Commands\Common\ModelGeneratorCommand;
use PWWEB\Artomator\Commands\Common\RepositoryGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLMutationsGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLQueryGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLSubscriptionGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLTypeGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQLScaffoldGeneratorCommand;
use PWWEB\Artomator\Commands\Publish\GeneratorPublishCommand;
use PWWEB\Artomator\Commands\Publish\LayoutPublishCommand;
use PWWEB\Artomator\Commands\Publish\PublishTemplateCommand;
use PWWEB\Artomator\Commands\Publish\PublishUserCommand;
use PWWEB\Artomator\Commands\RollbackGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\ControllerGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\RequestsGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\RoutesGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\ScaffoldGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\ViewsGeneratorCommand;

class ArtomatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__.'/../config/artomator.php';
        $configPathInfyom = __DIR__.'/../../../infyomlabs/laravel-generator/config/laravel_generator.php';
        $schemaPath = __DIR__.'/../../../nuwave/lighthouse/src/default-schema.graphql';
        $configPathNuwave = __DIR__.'/../../../nuwave/lighthouse/src/lighthouse.php';

        $this->publishes(
            [
                $configPath       => config_path('pwweb/artomator.php'),
                $configPathInfyom => config_path('infyom/laravel_generator.php'),
                $configPathNuwave => config_path('lighthouse.php'),
                $schemaPath       => config('lighthouse.schema.register', base_path('graphql/schema.graphql')),
            ],
            'artomator'
        );

        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'artomator.publish',
            function ($app) {
                return new GeneratorPublishCommand();
            }
        );

        $this->app->singleton(
            'artomator.api',
            function ($app) {
                return new APIGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold',
            function ($app) {
                return new ScaffoldGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.publish.layout',
            function ($app) {
                return new LayoutPublishCommand();
            }
        );

        $this->app->singleton(
            'artomator.publish.templates',
            function ($app) {
                return new PublishTemplateCommand();
            }
        );

        $this->app->singleton(
            'artomator.api_scaffold',
            function ($app) {
                return new APIScaffoldGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.migration',
            function ($app) {
                return new MigrationGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.model',
            function ($app) {
                return new ModelGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.repository',
            function ($app) {
                return new RepositoryGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.api.controller',
            function ($app) {
                return new APIControllerGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.api.requests',
            function ($app) {
                return new APIRequestsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.api.tests',
            function ($app) {
                return new TestsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold.controller',
            function ($app) {
                return new ControllerGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold.requests',
            function ($app) {
                return new RequestsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold.views',
            function ($app) {
                return new ViewsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold.routes',
            function ($app) {
                return new RoutesGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.rollback',
            function ($app) {
                return new RollbackGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.publish.user',
            function ($app) {
                return new PublishUserCommand();
            }
        );

        $this->app->singleton(
            'artomator.graphql',
            function ($app) {
                return new GraphQLGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.graphql_scaffold',
            function ($app) {
                return new GraphQLScaffoldGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.graphql.query',
            function ($app) {
                return new GraphQLQueryGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.graphql.mutations',
            function ($app) {
                return new GraphQLMutationsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.graphql.type',
            function ($app) {
                return new GraphQLTypeGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.graphql.subscription',
            function ($app) {
                return new GraphQLSubscriptionGeneratorCommand();
            }
        );

        parent::register();

        $this->commands(
            [
                'artomator.publish',
                'artomator.api',
                'artomator.scaffold',
                'artomator.api_scaffold',
                'artomator.publish.layout',
                'artomator.publish.templates',
                'artomator.migration',
                'artomator.model',
                'artomator.repository',
                'artomator.api.controller',
                'artomator.api.requests',
                'artomator.api.tests',
                'artomator.scaffold.controller',
                'artomator.scaffold.requests',
                'artomator.scaffold.views',
                'artomator.scaffold.routes',
                'artomator.rollback',
                'artomator.publish.user',
                'artomator.graphql',
                'artomator.graphql_scaffold',
                'artomator.graphql.query',
                'artomator.graphql.mutations',
                'artomator.graphql.type',
                'artomator.graphql.subscription',
            ]
        );
    }
}
