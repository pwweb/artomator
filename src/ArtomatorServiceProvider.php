<?php

namespace PWWEB\Artomator;

use InfyOm\Generator\InfyOmGeneratorServiceProvider as ServiceProvider;
use PWWEB\Artomator\Commands\GraphQL\GraphQLGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLMutationsGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLQueryGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLSubscriptionGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQL\GraphQLTypeGeneratorCommand;
use PWWEB\Artomator\Commands\GraphQLScaffoldGeneratorCommand;
use PWWEB\Artomator\Commands\Publish\PublishTemplateCommand;

class ArtomatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $configPath = __DIR__.'/../config/artomator.php';
        $configPathInfyom = __DIR__.'/../../../infyomlabs/laravel-generator/config/laravel_generator.php';

        $this->publishes(
            [
                $configPath       => config_path('pwweb/artomator.php'),
                $configPathInfyom => config_path('infyom/laravel_generator.php'),
            ],
            'artomator.config'
        );

        parent::boot();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
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

        $this->app->singleton('artomator.publish.templates', function ($app) {
            return new PublishTemplateCommand();
        });

        parent::register();

        $this->commands(
            [
                'artomator.graphql',
                'artomator.graphql_scaffold',
                'artomator.graphql.query',
                'artomator.graphql.mutations',
                'artomator.graphql.type',
                'artomator.graphql.subscription',
                'artomator.publish.templates',
            ]
        );
    }
}
