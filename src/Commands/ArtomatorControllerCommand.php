<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\GeneratorCommand;
use Laracasts\Generators\Migrations\SchemaParser;
use PWWEB\Artomator\Migrations\SyntaxBuilder;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorControllerCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

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
        $stub = 'controller.model.stub';
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
        return $rootNamespace.'\Http\Controllers';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        // if ($this->option('parent')) {
        //     $replace = $this->buildParentReplacements();
        // }
        $replace = $this->buildSchemaReplacements($replace);
        $replace = $this->buildModelReplacements($replace);

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Build the schema replacement values.
     *
     * @param array $replace
     * @return array
     */

    protected function buildSchemaReplacements(array $replace)
    {
        if ($schema = $this->option('schema')) {
            $schema = (new SchemaParser)->parse($schema);
        }

        $syntax = new SyntaxBuilder();

        return array_merge($replace, [
            '{{schema_data}}' => $syntax->createDataSchema($schema),
        ]);
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    // protected function buildParentReplacements()
    // {
    //     $parentModelClass = $this->parseModel($this->option('parent'));
    //
    //     if (! class_exists($parentModelClass)) {
    //         if ($this->confirm("A {$parentModelClass} model does not exist. Do you want to generate it?", true)) {
    //             $this->call('make:model', ['name' => $parentModelClass]);
    //         }
    //     }
    //
    //     return [
    //         'ParentDummyFullModelClass' => $parentModelClass,
    //         'ParentDummyModelClass' => class_basename($parentModelClass),
    //         'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
    //     ];
    // }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel((string) $this->option('model'));

        if (! class_exists($modelClass)) {
            if ($this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $modelClass]);
            }
        }

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            'DummyPackageVariable' => strtolower($this->package) . ".",
            'DummyPackagePlaceholder' => config('app.name'),
            'DummyCopyrightPlaceholder' => config('artomator.copyright'),
            'DummyLicensePlaceholder' => config('artomator.license'),
            'DummyAuthorPlaceholder' => $this->parseAuthors(config('artomator.authors')),
        ]);
    }

    /**
     * Get the formatted author(s) from the config file.
     *
     * @param  string[] $authors Authors array.
     *
     * @return string Formmated string of authors.
     */
    protected function parseAuthors($authors)
    {
        if (is_array($authors) === false and is_string($authors) === false) {
            throw new InvalidArgumentException('Authors must be an array of strings or a string.');
        }

        $formatted = '';

        if (is_array($authors) === true) {
            if (is_string($authors[0]) === false) {
                throw new InvalidArgumentException('The array of authors must be strings.');
            }
            $formatted .= array_shift($authors);

            foreach ($authors as $author) {
                if (is_string($author) === false) {
                    throw new InvalidArgumentException('The array of authors must be strings.');
                }
                $formatted .= "\n * @author    " . $author;
            }
        } else {
            $formatted .= $authors;
        }

        return $formatted;
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

        $this->package = trim(str_replace('/', '.', substr($model, 0, strrpos($model, '/')))) ?? null;

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
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Generate a resource controller for the given model.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class.'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class.'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller class.'],
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
            ['schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null],
        ];
    }
}
