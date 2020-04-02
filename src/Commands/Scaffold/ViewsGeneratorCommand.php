<?php

namespace PWWEB\Artomator\Commands\Scaffold;

use InfyOm\Generator\Commands\Scaffold\ViewsGeneratorCommand as Base;
use PWWEB\Artomator\Common\CommandData;

class ViewsGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.scaffold:views';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_SCAFFOLD);
    }
}
