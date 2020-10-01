<?php

namespace PWWEB\Artomator\Common;

use Illuminate\Console\Command;
use InfyOm\Generator\Common\CommandData as Data;
use InfyOm\Generator\Common\TemplatesManager;

class CommandData extends Data
{
    public static $COMMAND_TYPE_GRAPHQL = 'graphql';
    public static $COMMAND_TYPE_GRAPHQL_SCAFFOLD = 'graphql_scaffold';

    /**
     * @param Command          $commandObj
     * @param string|string[]  $commandType
     * @param TemplatesManager $templatesManager
     */
    public function __construct(Command $commandObj, $commandType, TemplatesManager $templatesManager = null)
    {
        parent::__construct($commandObj, $commandType, $templatesManager);

        $this->config = new GeneratorConfig();
    }
}
