<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
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

    protected $includes = [
        'model',
        'controller',
        'request',
        'query',
        'type',
        'migration',
        'seeder',
        'factory',
    ];

    protected $schema;

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
     * @return boolean
     */
    public function handle()
    {
        $this->parseIncludes();

        $this->schema = $this->option('schema');

        if ($this->option('table')) {
            $this->insepctTable($this->option('table'));
        }

        if (in_array('model', $this->includes) === true && parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        if (in_array('factory', $this->includes) === true) {
            $this->createFactory();
        }
        if (in_array('seeder', $this->includes) === true) {
            $this->createSeeder();
        }
        if (in_array('migration', $this->includes) === true) {
            $this->createMigration();
        }
        if (in_array('controller', $this->includes) === true) {
            $this->createController();
        }
        if (in_array('request', $this->includes) === true) {
            $this->createRequest();
        }
        if (in_array('query', $this->includes) === true) {
            $this->createQuery();
        }
        if (in_array('type', $this->includes) === true) {
            $this->createType();
        }
        return true;
    }

    protected function insepctTable($table)
    {
        $results = [];
        $columns = \Schema::getColumnListing($table);
        foreach ($columns as $key => $column) {
            $results[] = [
                'type' => \Schema::getColumnType($table, $column),
                'name' => $column,
            ];
        }
        dd($results);
    }

    protected function parseIncludes()
    {
        if ($this->option('exclude')) {
            $exclusions = explode(',', $this->option('exclude'));

            foreach ($exclusions as $exclusion) {
                unset($this->includes[array_search(trim($exclusion), $this->includes)]);
            }
        }
        if ($this->option('include')) {
            $inclusions = explode(',', $this->option('include'));

            foreach ($inclusions as &$inclusion) {
                $inclusion = trim($inclusion);
            }
            $this->includes = $inclusions;
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

        if ($this->schema) {
            $this->call('make:migration:schema', [
                'name' => "create_{$table}_table",
                '--schema' => $this->schema,
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
            '--schema' => $this->schema,
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
        $validator = Str::studly(class_basename((string) $this->argument('name')));

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
        $query = Str::pluralStudly((string) $this->argument('name'));

        $modelName = $this->getNameInput();

        $this->call('artomator:query', [
            'name' => "{$query}Query",
            '--model' => $modelName,
            '--schema' => $this->schema,
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
            '--schema' => $this->schema,
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
            ['include', 'i', InputOption::VALUE_OPTIONAL, 'Specify which "Generators" to run'],
            ['exclude', 'e', InputOption::VALUE_OPTIONAL, 'Specify which "Genertors" to exclude'],
            ['table', 't', InputOption::VALUE_OPTIONAL, 'Specify which table to inspect'],
        ];
    }
}
