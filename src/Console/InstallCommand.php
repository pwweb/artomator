<?php

namespace PWWEB\Artomator\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'artomator:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Artomator components and resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Starting Installation of Artomator.');
        $bar = $this->output->createProgressBar(4);
        $bar->start();
        // Publish...
        $this->callSilent(
            'vendor:publish',
            [
                '--tag' => 'artomator',
                '--force' => true,
            ]
        );
        $bar->advance();
        $this->callSilent(
            'vendor:publish',
            [
                '--provider' => 'InfyOm\Generator\InfyOmGeneratorServiceProvider',
                '--force' => true,
            ]
        );
        $bar->advance();
        $this->callSilent(
            'vendor:publish',
            [
                '--tag' => 'lighthouse-schema',
                '--force' => true,
            ]
        );
        $bar->advance();
        $this->callSilent(
            'vendor:publish',
            [
                '--tag' => 'lighthouse-config',
                '--force' => true,
            ]
        );
        $bar->finish();
        $this->newLine();
        $this->info('Config files published. (4/4)');

        $this->call('artomator:publish');

        $this->info('Artomator base files published.');

        $aliases = [
            "'View' => Illuminate\Support\Facades\View::class",
            "'Form' => Collective\Html\FormFacade::class",
            "'Html' => Collective\Html\HtmlFacade::class",
            "'Flash' => Laracasts\Flash\Flash::class",
        ];

        $this->replaceInFile(
            "'View' => Illuminate\Support\Facades\View::class",
            implode(",\n\t\t", $aliases),
            config_path('app.php')
        );

        $template = $this->choice(
            'Choose your templating package',
            ['CoreUI', 'AdminLTE'],
            0
        );

        if ('CoreUI' === $template) {
            $this->replaceInFile(
                "'templates'         => 'adminlte-templates',",
                "'templates'         => 'coreui-templates',",
                config_path('infyom/laravel_generator.php')
            );
            $this->requireComposerPackages('infyomlabs/coreui-templates:^8.0.x-dev');
        } elseif ('AdminLTE' === $template) {
            $this->requireComposerPackages('infyomlabs/adminlte-templates:^8.0.x-dev');
        }
        $this->newLine();
        $this->info('Template package added to composer.');

        if (true === $this->confirm('Do you want to install Laravel Jetstream (Inertia & Vue)?')) {
            $this->requireComposerPackages('laravel/jetstream');
            if (true === $this->confirm('Do you want to support Laravel Jetstream Teams?')) {
                $this->call('jetstream:install', ['stack' => 'inertia', '--teams']);
            } else {
                $this->call('jetstream:install', ['stack' => 'inertia']);
            }
            $this->info('Laravel Jetstream Installed.');
        }

        if (true === $this->confirm('Do you want to publish the stub files?')) {
            $this->call('artomator.publish:templates');
            $this->info('Stub files published.');
        }
        $this->info('Thanks for installing Artomator.');
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param mixed $packages Packages to install.
     *
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ('global' !== $composer) {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            ($command ?? ['composer', 'require']),
            (true === is_array($packages)) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(
                function ($type, $output) {
                    $this->output->write($output);
                }
            );
    }

    /**
     * Update the "package.json" file.
     *
     * @param callable $callback Callback function.
     * @param bool     $dev      Dev.
     *
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (false === file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = (true === $dev) ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            (true === array_key_exists($configurationKey, $packages)) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)).PHP_EOL
        );
    }

    /**
     * Delete the "node_modules" directory and remove the associated lock files.
     *
     * @return void
     */
    protected static function flushNodeModules()
    {
        tap(
            new Filesystem,
            function ($files) {
                $files->deleteDirectory(base_path('node_modules'));

                $files->delete(base_path('yarn.lock'));
                $files->delete(base_path('package-lock.json'));
            }
        );
    }

    /**
     * Replace a given string within a given file.
     *
     * @param string $search  Search term.
     * @param string $replace Replace term.
     * @param string $path    Path.
     *
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
