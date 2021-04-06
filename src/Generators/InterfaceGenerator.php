<?php

namespace PWWEB\Artomator\Generators;

use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use PWWEB\Artomator\Common\CommandData;

class InterfaceGenerator extends BaseGenerator
{
    /**
     * Command Data.
     *
     * @var CommandData
     */
    private $commandData;

    /**
     * Path.
     *
     * @var string
     */
    private $path;

    /**
     * Filename.
     *
     * @var string
     */
    private $fileName;

    /**
     * Constructor.
     *
     * @param CommandData $commandData Command Data.
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathInterface;
        $this->fileName = $this->commandData->modelName.'RepositoryInterface.php';
    }

    /**
     * Generate.
     *
     * @return void
     */
    public function generate()
    {
        $templateData = get_artomator_template('interface');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $searchables = [];

        foreach ($this->commandData->fields as $field) {
            if (true === $field->isSearchable) {
                $searchables[] = "'".$field->name."'";
            }
        }

        $templateData = str_replace('$FIELDS$', implode(','.infy_nl_tab(1, 2), $searchables), $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nRepository Interface created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        if (true === $this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Repository Interface file deleted: '.$this->fileName);
        }
    }
}
