<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Console\GeneratorCommand;

class ArtomatorRequestCommand extends GeneratorCommand
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

        if (file_exists($path)) {
            return $path;
        } else {
            return __DIR__ . '/Stubs/' . $stub;
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Requests';
    }
}
