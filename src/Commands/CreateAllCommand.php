<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class CreateAllCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create:all';

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

        $stub = '/stubs/model.stub';

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

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
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

        $this->call('create:controller', [
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

        $this->call('create:request', [
            'name' => "Validate{$validator}",
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

        $this->call('create:query', [
            'name' => "{$query}Query",
            '--model' => $modelName,
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

        $this->call('create:type', [
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

            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],
        ];
    }
}
