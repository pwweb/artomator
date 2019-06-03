<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorQueryCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new query class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Query';

    /**
     * The package of class being generated.
     *
     * @var string
     */
    protected $package = null;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = '/stubs/query.stub';

        return __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\GraphQL\Query';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base query import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $queryNamespace = $this->getNamespace($name);

        $replace = [];
        $replace = $this->buildModelReplacements($replace);

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyPluralModelClass' => Str::pluralStudly(class_basename($modelClass)),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            'DummyPackageVariable' => lcfirst($this->package) . ".",
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $this->package = strstr($model, '/', TRUE) ?? null;

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (! Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace.'Models\\'.$model;
        }

        return $model;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource query for the given model.'],
        ];
    }
}
