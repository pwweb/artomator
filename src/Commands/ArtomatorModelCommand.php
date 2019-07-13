<?php

namespace PWWEB\Artomator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ArtomatorModelCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * The schema variable.
     *
     * @var string
     */
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

        if (file_exists($path) === true) {
            return $path;
        } else {
            return __DIR__ . '/Stubs/' . $stub;
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace The class name to return FQN for.
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Models';
    }

    /**
     * Execute the console command.
     *
     * @return boolean
     */
    public function handle()
    {

        $this->schema = $this->option('schema');

        if (parent::handle() === false and $this->option('force') !== false) {
            return false;
        }

        return true;
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param string $name The name of the model to build.
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $table = Str::snake(Str::pluralStudly(str_replace('/', '', $this->argument('name'))));

        $replace = [];

        $replace = array_merge(
            $replace,
            [
            'DummyFullModelClass' => $this->qualifyClass($name),
            'DummyPackagePlaceholder' => config('app.name'),
            'DummySnakeCaseClass' => $table,
            'DummyCopyrightPlaceholder' => config('artomator.copyright'),
            'DummyLicensePlaceholder' => config('artomator.license'),
            'DummyAuthorPlaceholder' => $this->parseAuthors(config('artomator.authors')),
            ]
        );

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null],
            ['force', 'f', InputOption::VALUE_OPTIONAL, 'Force the generation of the model again', false]
        ];
    }
}
