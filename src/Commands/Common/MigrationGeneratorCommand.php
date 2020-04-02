<?php

namespace PWWEB\Artomator\Commands\Common;

use InfyOm\Generator\Commands\Common\MigrationGeneratorCommand as Base;
use PWWEB\Artomator\Common\CommandData;

class MigrationGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:migration';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }
}
