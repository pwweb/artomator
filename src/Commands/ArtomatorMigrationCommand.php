<?php

namespace PWWEB\Artomator\Commands;

use Laracasts\Generators\Commands\MigrationMakeCommand as BaseCommand;
use Laracasts\Generators\Migrations\NameParser;

class ArtomatorMigrationCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:migration';

    /**
     * Execute the console command.
     *
     * @return boolean
     */
    public function handle()
    {
        $this->meta = (new NameParser)->parse($this->argument('name'));

        parent::makeMigration();
    }
}
