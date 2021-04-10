<?php

namespace PWWEB\Artomator\Common;

use Illuminate\Console\Command;
use InfyOm\Generator\Common\CommandData as Data;
use InfyOm\Generator\Common\TemplatesManager;

class CommandData extends Data
{
    /**
     * Command type graphql.
     *
     * @var string
     */
    public static $COMMAND_TYPE_GRAPHQL = 'graphql';
    /**
     * Command type graphql_scaffold.
     *
     * @var string
     */
    public static $COMMAND_TYPE_GRAPHQL_SCAFFOLD = 'graphql_scaffold';

    /**
     * Constructor.
     *
     * @param Command          $commandObj       Command object.
     * @param string|string[]  $commandType      Commant type.
     * @param TemplatesManager $templatesManager Template Manager.
     */
    public function __construct(Command $commandObj, $commandType, TemplatesManager $templatesManager = null)
    {
        parent::__construct($commandObj, $commandType, $templatesManager);

        $this->config = new GeneratorConfig();
    }
}
