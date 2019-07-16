<?php

namespace PWWEB\Artomator\Commands;

use InvalidArgumentException;
use PWWEB\Artomator\Artomator;
use Laracasts\Generators\Migrations\SchemaParser;
use PWWEB\Artomator\Migrations\SyntaxBuilder;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorControllerCommand extends Artomator
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

    public function handle()
    {
        $name = $this->getNameInput();
        $name .= preg_match('/Controller$/', $name) ? "" : "Controller";

        $className = $this->qualifyClass($name);
        $path = $this->getPath($className);
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') ||
             !$this->option('force')) &&
             $this->alreadyExists($name)) {
            $this->error($this->type . ' already exists!');
            return false;
        }
        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($className));
        $this->info($this->type . ' created successfully.');
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
            $syntax = new SyntaxBuilder();
            $data = $syntax->createDataSchema($schema);
        } else {
            $data = "";
        }

        return array_merge(
            $replace,
            [
            '{{schema_data}}' => $data,
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
    protected function buildModelReplacements(array $replace = [])
    {
        if (is_null($this->option('model')) === true) {
            $this->modelClass = $this->parseModel((string) $this->getNameInput());
            $modelName = $this->getNameInput();
            $this->requestClass = $this->parseRequest((string) $this->getNameInput());
        } else {
            $this->modelClass = $this->parseModel((string) $this->option('model'));
            $modelName = $this->option('model');
            $this->requestClass = $this->parseRequest((string) $this->option('model'));
        }

        if (class_exists($this->modelClass) === false) {
            if ($this->confirm("A {$this->modelClass} model does not exist. Do you want to generate it?", true) === true) {
                $this->call('artomator:model', [
                    'name' => $modelName,
                ]);
            } else {
                $this->stub = 'controller.stub';
            }
        }

        return parent::buildModelReplacements($replace);
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
