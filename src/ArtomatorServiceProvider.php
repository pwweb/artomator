<?php

namespace PWWEB\Artomator;

use Illuminate\Support\ServiceProvider;

class ArtomatorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'pwweb');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'pwweb');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/artomator.php', 'artomator');

        // Register the service the package provides.
        $this->app->singleton('artomator', function ($app) {
            return new Artomator;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['artomator'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/artomator.php' => config_path('artomator.php'),
        ], 'artomator.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/pwweb'),
        ], 'artomator.views');*/

        // Publishing stubs.
        $this->publishes([
            __DIR__.'/Commands/stubs' => public_path('vendor/pwweb'),
        ], 'artomator.stubs');

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/pwweb'),
        ], 'artomator.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/pwweb'),
        ], 'artomator.views');*/

        // Registering package commands.
        $this->commands([
            Commands\ArtomatorAllCommand::class,
            Commands\ArtomatorControllerCommand::class,
            Commands\ArtomatorQueryCommand::class,
            Commands\ArtomatorRequestCommand::class,
            Commands\ArtomatorTypeCommand::class,
        ]);
    }
}
