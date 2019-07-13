<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model, factory, migration, controller and resource class';

    /**
     * The array of standard included generators.
     *
     * @var string[]
     */
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

    /**
     * The schema variable.
     *
     * @var string
     */
    protected $schema;

    /**
     * Execute the console command.
     *
     * @return boolean
     */
    public function handle()
    {
        $this->info('
                         _/                                            _/
    _/_/_/  _/  _/_/  _/_/_/_/    _/_/    _/_/_/  _/_/      _/_/_/  _/_/_/_/    _/_/    _/  _/_/
 _/    _/  _/_/        _/      _/    _/  _/    _/    _/  _/    _/    _/      _/    _/  _/_/
_/    _/  _/          _/      _/    _/  _/    _/    _/  _/    _/    _/      _/    _/  _/
 _/_/_/  _/            _/_/    _/_/    _/    _/    _/    _/_/_/      _/_/    _/_/    _/

______________________________________________________________________________________________/');
        $this->name = $this->ask('What is the name of the model you want to build?
 Use the form: Primary/Secondary/Tertiary/Names');
        $this->name = $this->normaliseName($this->name);

        $this->parseExcludes();

        $this->schema = $this->option('schema');

        if ($this->option('table') !== null) {
            $this->schema = $this->insepctTable((string) $this->option('table'));
        }

        if (in_array('model', $this->includes) === true ) {
            $this->createModel();
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

    /**
     * Normalise the name input to capitalise each.
     *
     * @param  string $name Input given by user.
     *
     * @return string Normalised name input.
     */
    protected function normaliseName($name)
    {
        $name = explode('/', $name);
        foreach ($name as &$part) {
            $part = ucfirst($part);
        }
        $name = implode('/', $name);
        return $name;
    }

    /**
     * Inspect table with the database for schema.
     *
     * @param string $table The name of the table to insepct.
     *
     * @return string
     */
    protected function insepctTable($table)
    {
        if (\Schema::hasTable($table) !== true) {
            return '';
        }
        $results = [];
        $columns = \DB::connection()->select(\DB::raw('describe ' . $table));
        foreach ($columns as $column) {
            if ($column->Key === 'PRI') {
                // $primary = [
                //     'type' => $column->Type,
                //     'name' => $column->Field,
                // ];
                continue;
            }
            $results[] = [
                'type' => $column->Type,
                'name' => $column->Field,
                'key' => $column->Key === 'UNI' ? 'unique' : '',
            ];
        }

        $results = array_map(
            function ($field) {
                return $this->addArg($field);
            },
            $results
        );

        $results = implode(",", $results);

        return $results;
    }

    /**
     * Format the schema for passing to subsequent generators.
     *
     * @param array $field The field definition.
     *
     * @return string
     */
    private function addArg($field)
    {
        if ($field['key'] !== '') {
            $format = "%s:%s:%s";
        } else {
            $format = "%s:%s";
        }
        $syntax = sprintf($format, $field['name'], $this->normaliseType($field['type']), $field['key']);
        return $syntax;
    }

    /**
     * Normalise the type from the inspection to remove the additional information.
     *
     * @param string $type The field type definition.
     *
     * @return string
     */
    private function normaliseType($type)
    {
        $type = preg_replace("/(\\(.*\\))/is", '', $type);
        switch ($type) {
            case 'int':
            case 'int unsigned':
                $type = 'integer';
                break;
            case 'tinyint':
            case 'bool':
            case 'boolean':
                $type = 'boolean';
                break;
            case 'varchar':
            case 'string':
            default:
                $type = 'string';
                break;
        }

        return $type;
    }

    /**
     * Parse the includes and excludes arguments.
     *
     * @return string[]
     */
    protected function parseExcludes()
    {
        if ($this->option('exclude') !== null) {
            $exclusions = explode(',', $this->option('exclude'));

            foreach ($exclusions as $exclusion) {
                unset($this->includes[array_search(trim($exclusion), $this->includes)]);
            }
        }
    }

    /**
     * Create a model factory for the model.
     *
     * @return void
     */
    protected function createFactory()
    {
        $this->info('Creating Factory');
        $factory = $this->name;

        $this->call(
            'make:factory',
            [
            'name' => "{$factory}Factory",
            '--model' => "Models/" . $this->name,
            ]
        );
    }

    /**
     * Create a model.
     *
     * @return void
     */
    protected function createModel()
    {
        $this->info('Creating Model');
        $model = $this->name;

        $this->call(
            'artomator:model',
            [
            'name' => "{$model}",
            '--schema' => $this->schema,
            ]
        );
    }

    /**
     * Create a model seeder for the model.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $this->info('Creating Seeder');
        $seeder = str_replace('/', '', $this->name);

        $this->call(
            'make:seeder',
            [
            'name' => "{$seeder}TableSeeder",
            ]
        );
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $this->info('Creating Migration');
        $table = Str::snake(Str::pluralStudly(str_replace('/', '', $this->name)));

        if ($this->schema !== '') {
            $this->call(
                'artomator:migration',
                [
                'name' => "create_{$table}_table",
                '--schema' => $this->schema,
                ]
            );
        } else {
            $this->call(
                'make:migration',
                [
                'name' => "create_{$table}_table",
                '--create' => $table,
                ]
            );
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
        $controller = $this->name;

        $modelName = $this->name;

        $this->call(
            'artomator:controller',
            [
            'name' => "{$controller}Controller",
            '--model' => $modelName,
            '--schema' => $this->schema,
            ]
        );
    }

    /**
     * Create a request for the model.
     *
     * @return void
     */
    protected function createRequest()
    {
        $this->info('Creating Request');

        $this->call(
            'artomator:request',
            [
            'name' => $this->name,
            '--model' => $this->name,
            ]
        );
    }

    /**
     * Create a query for the model.
     *
     * @return void
     */
    protected function createQuery()
    {
        $this->info('Creating Query');
        $query = Str::pluralStudly((string) $this->name);

        $modelName = $this->name;

        $this->call(
            'artomator:query',
            [
            'name' => "{$query}",
            '--model' => $modelName,
            '--schema' => $this->schema,
            ]
        );
    }

    /**
     * Create a type for the model.
     *
     * @return void
     */
    protected function createType()
    {
        $this->info('Creating Type');
        $type = $this->name;

        $modelName = $this->name;

        $this->call(
            'artomator:type',
            [
            'name' => "{$type}",
            '--model' => $modelName,
            '--schema' => $this->schema,
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
            ['schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null],
            ['exclude', 'e', InputOption::VALUE_OPTIONAL, 'Specify which "Genertors" to exclude'],
            ['table', 't', InputOption::VALUE_OPTIONAL, 'Specify which table to inspect'],
        ];
    }
}
