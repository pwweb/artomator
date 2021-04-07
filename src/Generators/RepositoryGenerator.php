<?php

namespace PWWEB\Artomator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class RepositoryGenerator extends BaseGenerator
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
     * @param CommandData $commandData Command data.
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRepository;
        $this->fileName = $this->commandData->modelName.'Repository.php';
    }

    /**
     * Generate.
     *
     * @return void
     */
    public function generate()
    {
        $templateData = get_artomator_template('repository');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $searchables = [];

        foreach ($this->commandData->fields as $field) {
            if (true === $field->isSearchable) {
                $searchables[] = "'".$field->name."'";
            }
        }

        $templateData = str_replace('$FIELDS$', implode(','.infy_nl_tab(1, 2), $searchables), $templateData);

        $docsTemplate = get_artomator_template('docs.repository');
        $docsTemplate = fill_template($this->commandData->dynamicVars, $docsTemplate);
        $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);

        $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nRepository created: ");
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
            $this->commandData->commandComment('Repository file deleted: '.$this->fileName);
        }
    }
}
