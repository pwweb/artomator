<?php

namespace PWWEB\Artomator\Generators\Scaffold;

use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\BaseGenerator;
use PWWEB\Artomator\Utils\FileUtil;

class ControllerGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var string */
    private $fileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathController;
        $this->templateType = config('pwweb.artomator.templates', 'adminlte-templates');
        $this->fileName = $this->commandData->modelName.'Controller.php';
    }

    public function generate()
    {
        if ($this->commandData->getAddOn('datatables')) {
            if ($this->commandData->getOption('repositoryPattern')) {
                $templateName = 'datatable_controller';
            } else {
                $templateName = 'model_datatable_controller';
            }

            $templateData = get_template("scaffold.controller.$templateName", 'artomator');
            $this->generateDataTable();
        } else {
            if ($this->commandData->getOption('repositoryPattern')) {
                $templateName = 'controller';
            } else {
                $templateName = 'model_controller';
            }

            $templateData = get_template("scaffold.controller.$templateName", 'artomator');
            $paginate = $this->commandData->getOption('paginate');

            if ($paginate) {
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

    private function generateDataTable()
    {
        $templateData = get_template('scaffold.datatable', 'artomator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace(
            '$DATATABLE_COLUMNS$',
            implode(','.arty_nl_tab(1, 3), $this->generateDataTableColumns()),
            $templateData
        );

        $path = $this->commandData->config->pathDataTables;

        $fileName = $this->commandData->modelName.'DataTable.php';

        FileUtil::createFile($path, $fileName, $templateData);

        $this->commandData->commandComment("\nDataTable created: ");
        $this->commandData->commandInfo($fileName);
    }

    private function generateDataTableColumns()
    {
        $headerFieldTemplate = get_template('scaffold.views.datatable_column', $this->templateType);

        $dataTableColumns = [];
        foreach ($this->commandData->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            $fieldTemplate = fill_template_with_field_data(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $headerFieldTemplate,
                $field
            );

            if ($field->isSearchable) {
                $dataTableColumns[] = $fieldTemplate;
            } else {
                $dataTableColumns[] = "'".$field->name."' => ['searchable' => false]";
            }
        }

        return $dataTableColumns;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Controller file deleted: '.$this->fileName);
        }

        if ($this->commandData->getAddOn('datatables')) {
            if ($this->rollbackFile($this->commandData->config->pathDataTables, $this->commandData->modelName.'DataTable.php')) {
                $this->commandData->commandComment('DataTable file deleted: '.$this->fileName);
            }
        }
    }
}
