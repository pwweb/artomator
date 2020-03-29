<?php

namespace PWWEB\Artomator\Commands;

use InfyOm\Generator\Commands\APIScaffoldGeneratorCommand as Base;
use PWWEB\Artomator\Common\CommandData;

class APIScaffoldGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:api_scaffold';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API_SCAFFOLD);
    }
}
