<?php

namespace PWWEB\Artomator\Commands\Scaffold;

use InfyOm\Generator\Commands\Scaffold\ScaffoldGeneratorCommand as Base;
use PWWEB\Artomator\Common\CommandData;

class ScaffoldGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:scaffold';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_SCAFFOLD);
    }
}
