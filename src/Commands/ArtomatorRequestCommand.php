<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

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
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $modelClass = $this->parseModel($this->option('model'));

        $replace = [];
        $replace = array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyPackagePlaceholder' => config('app.name'),
            'DummyCopyrightPlaceholder' => config('artomator.copyright'),
            'DummyLicensePlaceholder' => config('artomator.license'),
            'DummyAuthorPlaceholder' => $this->parseAuthors(config('artomator.authors')),
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Get the formatted author(s) from the config file.
     *
     * @param  array[string] $authors Authors array.
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
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Requests';
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
