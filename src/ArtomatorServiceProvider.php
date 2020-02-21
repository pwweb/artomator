<?php

namespace PWWEB\Artomator;

use Illuminate\Support\ServiceProvider;
use PWWEB\Artomator\Commands\API\APIQueryGeneratorCommand;
use PWWEB\Artomator\Commands\API\APIGeneratorCommand;
use PWWEB\Artomator\Commands\API\APIMutationsGeneratorCommand;
use PWWEB\Artomator\Commands\API\APITypeGeneratorCommand;
use PWWEB\Artomator\Commands\API\APIConfigGeneratorCommand;
use PWWEB\Artomator\Commands\APIScaffoldGeneratorCommand;
use PWWEB\Artomator\Commands\Common\MigrationGeneratorCommand;
use PWWEB\Artomator\Commands\Common\ModelGeneratorCommand;
use PWWEB\Artomator\Commands\Common\RepositoryGeneratorCommand;
use PWWEB\Artomator\Commands\Publish\GeneratorPublishCommand;
use PWWEB\Artomator\Commands\Publish\LayoutPublishCommand;
use PWWEB\Artomator\Commands\Publish\PublishTemplateCommand;
use PWWEB\Artomator\Commands\Publish\VueJsLayoutPublishCommand;
use PWWEB\Artomator\Commands\RollbackGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\ControllerGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\RequestsGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\ScaffoldGeneratorCommand;
use PWWEB\Artomator\Commands\Scaffold\ViewsGeneratorCommand;
use PWWEB\Artomator\Commands\VueJs\VueJsGeneratorCommand;

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

        $this->publishes(
            [
            $configPath => config_path('pwweb/artomator.php'),
            ]
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'artomator.publish', function ($app) {
                return new GeneratorPublishCommand();
            }
        );

        $this->app->singleton(
            'artomator.api', function ($app) {
                return new APIGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold', function ($app) {
                return new ScaffoldGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.publish.layout', function ($app) {
                return new LayoutPublishCommand();
            }
        );

        $this->app->singleton(
            'artomator.publish.templates', function ($app) {
                return new PublishTemplateCommand();
            }
        );

        $this->app->singleton(
            'artomator.api_scaffold', function ($app) {
                return new APIScaffoldGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.migration', function ($app) {
                return new MigrationGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.model', function ($app) {
                return new ModelGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.repository', function ($app) {
                return new RepositoryGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.api.query', function ($app) {
                return new APIQueryGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.api.mutations', function ($app) {
                return new APIMutationsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.api.type', function ($app) {
                return new APITypeGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.api.config', function ($app) {
                return new APIConfigGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold.controller', function ($app) {
                return new ControllerGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold.requests', function ($app) {
                return new RequestsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.scaffold.views', function ($app) {
                return new ViewsGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.rollback', function ($app) {
                return new RollbackGeneratorCommand();
            }
        );

        $this->app->singleton(
            'artomator.vuejs', function ($app) {
                return new VueJsGeneratorCommand();
            }
        );
        $this->app->singleton(
            'artomator.publish.vuejs', function ($app) {
                return new VueJsLayoutPublishCommand();
            }
        );

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
            'artomator.api.query',
            'artomator.api.mutations',
            'artomator.api.type',
            'artomator.api.config',
            'artomator.scaffold.controller',
            'artomator.scaffold.requests',
            'artomator.scaffold.views',
            'artomator.rollback',
            'artomator.vuejs',
            'artomator.publish.vuejs',
            ]
        );
    }
}
