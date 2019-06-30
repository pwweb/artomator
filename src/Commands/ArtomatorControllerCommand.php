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
     * The default stub file to be used.
     *
     * @var string
     */
    protected $stub = 'controller.model.stub';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $path = base_path() . config('artomator.stubPath');
        $path = $path . $this->stub;

        if (file_exists($path) === true) {
            return $path;
        } else {
            return __DIR__ . '/Stubs/' . $this->stub;
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace The class name to return the namespace for.
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Controllers';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param string $name The model name to build.
     *
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
     * @param array $replace The existing replacements to append to.
     *
     * @return array
     */
    protected function buildSchemaReplacements(array $replace)
    {
        if ($this->option('schema') !== null) {
            $schema = $this->option('schema');
            $schema = (new SchemaParser())->parse($schema);
        } else {
            return $replace;
        }

        $syntax = new SyntaxBuilder();

        return array_merge(
            $replace,
            [
            '{{schema_data}}' => $syntax->createDataSchema($schema),
            ]
        );
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace The existing replacements to append to.
     *
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        if (is_null($this->option('model')) === true) {
            $modelClass = $this->parseModel((string) $this->getNameInput());
            $requestClass = $this->parseRequest((string) $this->getNameInput());
        } else {
            $modelClass = $this->parseModel((string) $this->option('model'));
            $requestClass = $this->parseRequest((string) $this->option('model'));
        }

        if (class_exists($modelClass) === false) {
            if ($this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true) === true) {
                $this->call('artomator:all', ['name' => $modelClass, 'include' => 'model']);
            } else {
                $this->stub = 'controller.stub';
            }
        }

        return array_merge(
            $replace,
            [
            'DummyFullModelClass' => $modelClass,
            'DummyRequestClass' => $requestClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            'DummyPackageVariable' => $this->package,
            'DummyPackagePlaceholder' => config('app.name'),
            'DummyCopyrightPlaceholder' => config('artomator.copyright'),
            'DummyLicensePlaceholder' => config('artomator.license'),
            'DummyAuthorPlaceholder' => $this->parseAuthors(config('artomator.authors')),
            ]
        );
    }

    /**
     * Get the formatted author(s) from the config file.
     *
     * @param string[] $authors Authors array.
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
     * @param string $model The model to return the FQN for.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model) === true) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $this->package = (trim(str_replace('/', '.', substr($model, 0, strrpos($model, '/')))) ?? null);
        $this->package = (empty($this->package) === true ? $this->package : (strtolower($this->package) . "."));

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace()) === false) {
            $model = $rootNamespace . 'Models\\' . $model;
        }

        return $model;
    }

    /**
     * Get the fully-qualified request class name.
     *
     * @param string $model The model to return the FQN for.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseRequest($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model) === true) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace()) === false) {
            $model = $rootNamespace . 'Http\\Requests\\' . $model;
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
