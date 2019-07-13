<?php

namespace PWWEB\Artomator;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

abstract class Artomator extends GeneratorCommand
{
    /**
     * The package of class being generated.
     *
     * @var string
     */
    protected $package = null;

    /**
     * Model class name.
     *
     * @var string
     */
    protected $modelClass = '';

    /**
     * Request class name.
     *
     * @var string
     */
    protected $requestClass = '';

    /**
     * Build the model replacement values.
     *
     * @param array $replace The existing replacements to append to.
     *
     * @return array
     */
    protected function buildModelReplacements(array $replace = [])
    {
        if($this->modelClass == '') {
            $this->modelClass = $this->parseModel((string) $this->getNameInput());
        }

        if ($this->requestClass == '') {
            $this->requestClass = $this->parseRequest((string) $this->getNameInput());
        }

        $table = Str::snake(Str::pluralStudly(str_replace('/', '', $this->argument('name'))));

        return array_merge(
            $replace,
            [
            'DummyFullModelClass' => $this->modelClass,
            'DummyRequestClass' => $this->requestClass,
            'DummyModelClass' => class_basename($this->modelClass),
            'DummyModelVariable' => lcfirst(class_basename($this->modelClass)),
            'DummyPluralModelClass' => Str::pluralStudly(class_basename($this->modelClass)),
            'DummySnakeCaseClass' => $table,
            'DummyPackageVariable' => lcfirst($this->package) . ".",
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
     * @param string $model The model name to return the FQN for.
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

        $this->package = (strstr($model, '/', true) ?? null);

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
}
