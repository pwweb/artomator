<?php

namespace PWWEB\Artomator\Generators;

use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use PWWEB\Artomator\Common\CommandData;

class InterfaceGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathInterface;
        $this->fileName = $this->commandData->modelName.'RepositoryInterface.php';
    }

    public function generate()
    {
        $templateData = get_artomator_template('interface');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $searchables = [];

        foreach ($this->commandData->fields as $field) {
            if ($field->isSearchable) {
                $searchables[] = "'".$field->name."'";
            }
        }

        $templateData = str_replace('$FIELDS$', implode(','.infy_nl_tab(1, 2), $searchables), $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nRepository Interface created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Repository Interface file deleted: '.$this->fileName);
        }
    }
}
