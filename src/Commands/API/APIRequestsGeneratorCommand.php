<?php

namespace PWWEB\Artomator\Commands\API;

use InfyOm\Generator\Commands\API\APIRequestsGeneratorCommand as Base;
use PWWEB\Artomator\Common\CommandData;

class APIRequestsGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.api:requests';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }
}
