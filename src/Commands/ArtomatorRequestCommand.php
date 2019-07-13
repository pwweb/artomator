<?php

namespace PWWEB\Artomator\Commands;

use PWWEB\Artomator\Artomator;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorRequestCommand extends Artomator
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form request class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = 'request.stub';
        $path = base_path() . config('artomator.stubPath');
        $path = $path . $stub;

        if (file_exists($path) === true) {
            return $path;
        } else {
            return __DIR__ . '/Stubs/' . $stub;
        }
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param string $name Name of Request to build.
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = parent::buildModelReplacements();

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace The class to return the namespace for.
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Requests';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Model name for Validated Request.'],
        ];
    }
}
