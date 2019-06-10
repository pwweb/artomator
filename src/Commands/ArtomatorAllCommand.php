<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorAllCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model, factory, migration, controller and resource class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = 'model.stub';
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
        return $rootNamespace.'\Models';
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        $this->createFactory();
        $this->createSeeder();
        $this->createMigration();
        $this->createController();
        $this->createRequest();
        $this->createQuery();
        $this->createType();
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
        $modelNamespace = $this->getNamespace($name);

        $table = Str::snake(Str::pluralStudly(str_replace('/', '', $this->argument('name'))));

        $replace = [];

        $replace = array_merge($replace, [
            'DummyFullModelClass' => $this->qualifyClass($name),
            'DummyPackagePlaceholder' => config('app.name'),
            'DummySnakeCaseClass' => $table,
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
     * Create a model factory for the model.
     *
     * @return void
     */
    protected function createFactory()
    {
        $this->info('Creating Factory');
        $factory = $this->argument('name');

        $this->call('make:factory', [
            'name' => "{$factory}Factory",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * Create a model seeder for the model.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $this->info('Creating Seeder');
        $seeder = str_replace('/', '', $this->argument('name'));

        $this->call('make:seeder', [
            'name' => "{$seeder}TableSeeder",
        ]);
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $this->info('Creating Migration');
        $table = Str::snake(Str::pluralStudly(str_replace('/', '', $this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }

        if ($this->option('schema')) {
            $this->call('make:migration:schema', [
                'name' => "create_{$table}_table",
                '--schema' => $this->option('schema'),
            ]);
        } else {
            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
            ]);
        }
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController()
    {
        $this->info('Creating Controller');
        $controller = $this->argument('name');

        $modelName = $this->getNameInput();

        $this->call('artomator:controller', [
            'name' => "{$controller}Controller",
            '--model' => $modelName,
        ]);
    }

    /**
     * Create a request for the model.
     *
     * @return void
     */
    protected function createRequest()
    {
        $this->info('Creating Request');
        $validator = Str::studly(class_basename($this->argument('name')));

        $this->call('artomator:request', [
            'name' => "Validate{$validator}",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * Create a query for the model.
     *
     * @return void
     */
    protected function createQuery()
    {
        $this->info('Creating Query');
        $query = Str::pluralStudly($this->argument('name'));

        $modelName = $this->getNameInput();

        $this->call('artomator:query', [
            'name' => "{$query}Query",
            '--model' => $modelName,
            '--schema' => $this->option('schema'),
        ]);
    }

    /**
     * Create a type for the model.
     *
     * @return void
     */
    protected function createType()
    {
        $this->info('Creating Type');
        $type = $this->argument('name');

        $modelName = $this->getNameInput();

        $this->call('artomator:type', [
            'name' => "{$type}Type",
            '--model' => $modelName,
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null],
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],
        ];
    }
}
