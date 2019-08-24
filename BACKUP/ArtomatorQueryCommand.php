<?php

namespace PWWEB\Artomator\Commands;

use PWWEB\Artomator\Artomator;
use Laracasts\Generators\Migrations\SchemaParser;
use PWWEB\Artomator\Migrations\SyntaxBuilder;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorQueryCommand extends Artomator
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
        $stub = 'query.stub';
        $path = base_path() . config('artomator.stubPath');
        $path = $path . $stub;

        if (file_exists($path) === true) {
            return $path;
        } else {
            return __DIR__ . '/Stubs/' . $stub;
        }
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
        return $rootNamespace . '\GraphQL\Query';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base query import if we are already in base namespace.
     *
     * @param string $name The name of the Query to build.
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];
        $replace = $this->buildSchemaReplacements($replace);
        $replace = $this->buildModelReplacements($replace);

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
            $syntax = new SyntaxBuilder();
            $args = $syntax->createArgsSchema($schema);
            $resolves = $syntax->createResolvesSchema($schema);
        } else {
            $args = "";
            $resolves = "";
        }

        return array_merge(
            $replace,
            [
            '{{schema_args}}' => $args,
            '{{schema_resolves}}' => $resolves,
            ]
        );
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
            ['schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null],
        ];
    }
}
