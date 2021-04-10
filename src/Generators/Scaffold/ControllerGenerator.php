<?php

namespace Pwweb\Artomator\Generators\Scaffold;

use InfyOm\Generator\Generators\Scaffold\ControllerGenerator as Base;
use InfyOm\Generator\Utils\FileUtil;
use PWWEB\Artomator\Common\CommandData;

class ControllerGenerator extends Base
{
    /**
     * Command data.
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
     * Template type.
     *
     * @var string
     */
    private $templateType;

    /**
     * File name.
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
        $this->path = $commandData->config->pathController;
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
        $this->fileName = $this->commandData->modelName.'Controller.php';
    }

    /**
     * Generate.
     *
     * @return void
     */
    public function generate()
    {
        if (true === $this->commandData->getAddOn('datatables')) {
            if (true === $this->commandData->getOption('repositoryPattern')) {
                $templateName = 'datatable_controller';
            } else {
                $templateName = 'model_datatable_controller';
            }

            if (true === $this->commandData->isLocalizedTemplates()) {
                $templateName .= '_locale';
            }

            $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

            parent::generateDataTable();
        } elseif (true === $this->commandData->jqueryDT()) {
            $templateName = 'jquery_datatable_controller';
            $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

            parent::generateDataTable();
        } else {
            if (true === $this->commandData->getOption('repositoryPattern')) {
                $templateName = 'controller';
            } else {
                $templateName = 'model_controller';
            }
            if (true === $this->commandData->isLocalizedTemplates()) {
                $templateName .= '_locale';
            }
            if (true === $this->commandData->getOption('vue')) {
                $templateName .= '_inertia';
            }

            $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

            $paginate = $this->commandData->getOption('paginate');

            if (true === $paginate) {
                $templateData = str_replace('$RENDER_TYPE$', 'paginate('.$paginate.')', $templateData);
            } else {
                $templateData = str_replace('$RENDER_TYPE$', 'all()', $templateData);
            }
        }

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nController created: ");
        $this->commandData->commandInfo($this->fileName);
    }
}
