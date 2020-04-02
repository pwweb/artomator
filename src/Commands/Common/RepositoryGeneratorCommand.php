<?php

namespace PWWEB\Artomator\Commands\Common;

use InfyOm\Generator\Commands\Common\RepositoryGeneratorCommand as Base;
use PWWEB\Artomator\Common\CommandData;

class RepositoryGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:repository';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }
}
