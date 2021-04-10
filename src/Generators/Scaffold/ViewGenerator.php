<?php

namespace PWWEB\Artomator\Generators\Scaffold;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\HTMLFieldGenerator;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\ViewServiceProviderGenerator;

class ViewGenerator extends BaseGenerator
{
    /**
     * Command data.
     *
     * @var CommandData
     */
    private $commandData;

    /**
     * Path string.
     *
     * @var string
     */
    private $path;

    /**
     * Template Type.
     *
     * @var string
     */
    private $templateType;

    /**
     * HTML Fields.
     *
     * @var array
     */
    private $htmlFields;

    /**
     * Constructor.
     *
     * @param CommandData $commandData Command data.
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathViews;
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
    }

    /**
     * Generate files.
     *
     * @return void
     */
    public function generate()
    {
        if (false === file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $htmlInputs = Arr::pluck($this->commandData->fields, 'htmlInput');
        if (true === in_array('file', $htmlInputs)) {
            $this->commandData->addDynamicVariable('$FILES$', ", 'files' => true");
        }

        $this->commandData->commandComment("\nGenerating Views...");

        if (true === $this->commandData->getOption('views')) {
            $viewsToBeGenerated = explode(',', $this->commandData->getOption('views'));

            if (true === in_array('index', $viewsToBeGenerated)) {
                $this->generateTable();
                $this->generateIndex();
            }

            if (count(array_intersect(['create', 'update'], $viewsToBeGenerated)) > 0) {
                $this->generateFields();
            }

            if (true === in_array('create', $viewsToBeGenerated)) {
                $this->generateCreate();
            }

            if (true === in_array('edit', $viewsToBeGenerated)) {
                $this->generateUpdate();
            }

            if (true === in_array('show', $viewsToBeGenerated)) {
                $this->generateShowFields();
                $this->generateShow();
            }
        } else {
            $this->generateTable();
            $this->generateIndex();
            $this->generateFields();
            $this->generateCreate();
            $this->generateUpdate();
            $this->generateShowFields();
            $this->generateShow();
        }

        $this->commandData->commandComment('Views created: ');
    }

    /**
     * Generate Table.
     *
     * @return void
     */
    private function generateTable()
    {
        if (true === $this->commandData->getAddOn('datatables')) {
            $templateData = $this->generateDataTableBody();
            $this->generateDataTableActions();
        } else {
            $templateData = $this->generateBladeTableBody();
        }

        FileUtil::createFile($this->path, 'table.blade.php', $templateData);

        $this->commandData->commandInfo('table.blade.php created');
    }

    /**
     * Generate Data Table Body.
     *
     * @return void
     */
    private function generateDataTableBody()
    {
        $templateData = get_artomator_template('scaffold.views.datatable_body');

        return fill_template($this->commandData->dynamicVars, $templateData);
    }

    /**
     * Generate Data Table Actions.
     *
     * @return void
     */
    private function generateDataTableActions()
    {
        $templateName = 'datatables_actions';

        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.views.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'datatables_actions.blade.php', $templateData);

        $this->commandData->commandInfo('datatables_actions.blade.php created');
    }

    /**
     * Generate Blade Table Body.
     *
     * @return void
     */
    private function generateBladeTableBody()
    {
        $templateName = 'blade_table_body';

        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.views.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELD_HEADERS$', $this->generateTableHeaderFields(), $templateData);

        $cellFieldTemplate = get_artomator_template('scaffold.views.table_cell');

        $tableBodyFields = [];

        foreach ($this->commandData->fields as $field) {
            if (false === $field->inIndex) {
                continue;
            }

            $tableBodyFields[] = fill_template_with_field_data(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $cellFieldTemplate,
                $field
            );
        }

        $tableBodyFields = implode(infy_nl_tab(1, 3), $tableBodyFields);

        return str_replace('$FIELD_BODY$', $tableBodyFields, $templateData);
    }

    /**
     * Generate Table Header Fields.
     *
     * @return void
     */
    private function generateTableHeaderFields()
    {
        $templateName = 'table_header';

        $localized = false;
        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
            $localized = true;
        }

        $headerFieldTemplate = get_artomator_template('scaffold.views.'.$templateName);

        $headerFields = [];

        foreach ($this->commandData->fields as $field) {
            if (false === $field->inIndex) {
                continue;
            }

            if (true === $localized) {
                $headerFields[] = $fieldTemplate = fill_template_with_field_data_locale(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $headerFieldTemplate,
                    $field
                );
            } else {
                $headerFields[] = $fieldTemplate = fill_template_with_field_data(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $headerFieldTemplate,
                    $field
                );
            }
        }

        return implode(infy_nl_tab(1, 2), $headerFields);
    }

    /**
     * Generate Index.
     *
     * @return void
     */
    private function generateIndex()
    {
        $templateName = 'index';

        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.views.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        if (true === $this->commandData->getAddOn('datatables')) {
            $templateData = str_replace('$PAGINATE$', '', $templateData);
        } else {
            $paginate = $this->commandData->getOption('paginate');

            if (true === $paginate) {
                $paginateTemplate = get_artomator_template('scaffold.views.paginate');

                $paginateTemplate = fill_template($this->commandData->dynamicVars, $paginateTemplate);

                $templateData = str_replace('$PAGINATE$', $paginateTemplate, $templateData);
            } else {
                $templateData = str_replace('$PAGINATE$', '', $templateData);
            }
        }

        FileUtil::createFile($this->path, 'index.blade.php', $templateData);

        $this->commandData->commandInfo('index.blade.php created');
    }

    /**
     * Generate Fields.
     *
     * @return void
     */
    private function generateFields()
    {
        $templateName = 'fields';

        $localized = false;
        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
            $localized = true;
        }

        $this->htmlFields = [];

        foreach ($this->commandData->fields as $field) {
            if (false === $field->inForm) {
                continue;
            }

            $validations = explode('|', $field->validations);
            $minMaxRules = '';
            $required = '';
            foreach ($validations as $validation) {
                if (false === Str::contains($validation, ['max:', 'min:'])) {
                    continue;
                }

                $validationText = substr($validation, 0, 3);
                $sizeInNumber = substr($validation, 4);

                $sizeText = ('min' === $validationText) ? 'minlength' : 'maxlength';
                if ('number' === $field->htmlType) {
                    $sizeText = $validationText;
                }

                if (true === Str::contains($validation, 'required')) {
                    $required = ',\'required\' => true';
                }

                $size = ",'$sizeText' => $sizeInNumber";
                $minMaxRules .= $size;
            }

            $this->commandData->addDynamicVariable('$SIZE$', $minMaxRules);

            $this->commandData->addDynamicVariable('$REQUIRED$', $required);

            $fieldTemplate = HTMLFieldGenerator::generateHTML($field, $this->templateType, $localized);

            if ('selectTable' === $field->htmlType) {
                $inputArr = explode(',', $field->htmlValues[1]);
                $columns = '';
                foreach ($inputArr as $item) {
                    $columns .= "'$item'".',';
                    //e.g 'email,id,'
                }
                $columns = substr_replace($columns, '', -1);
                // remove last ,

                $htmlValues = explode(',', $field->htmlValues[0]);
                $selectTable = $htmlValues[0];
                $modalName = null;
                if (2 === count($htmlValues)) {
                    $modalName = $htmlValues[1];
                }

                $tableName = $this->commandData->config->tableName;
                $viewPath = $this->commandData->config->prefixes['view'];
                if (false === empty($viewPath)) {
                    $tableName = $viewPath.'.'.$tableName;
                }

                $variableName = Str::singular($selectTable).'Items';
                // e.g $userItems

                $fieldTemplate = $this->generateViewComposer($tableName, $variableName, $columns, $selectTable, $modalName);
            }

            if (false === empty($fieldTemplate)) {
                $fieldTemplate = fill_template_with_field_data(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $fieldTemplate,
                    $field
                );
                $this->htmlFields[] = $fieldTemplate;
            }
        }

        $templateData = get_artomator_template('scaffold.views.'.$templateName);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode("\n\n", $this->htmlFields), $templateData);

        FileUtil::createFile($this->path, 'fields.blade.php', $templateData);
        $this->commandData->commandInfo('field.blade.php created');
    }

    /**
     * Generate View Composer.
     *
     * @param string      $tableName    Table name.
     * @param string      $variableName Variable name.
     * @param string      $columns      Column names.
     * @param string      $selectTable  Select table name.
     * @param string|null $modelName    Model name.
     *
     * @return void
     */
    private function generateViewComposer($tableName, $variableName, $columns, $selectTable, $modelName = null)
    {
        $templateName = 'scaffold.fields.select';
        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_artomator_template($templateName);

        $viewServiceProvider = new ViewServiceProviderGenerator($this->commandData);
        $viewServiceProvider->generate();
        $viewServiceProvider->addViewVariables($tableName.'.fields', $variableName, $columns, $selectTable, $modelName);

        $fieldTemplate = str_replace(
            '$INPUT_ARR$',
            '$'.$variableName,
            $fieldTemplate
        );

        return $fieldTemplate;
    }

    /**
     * Generate Create.
     *
     * @return void
     */
    private function generateCreate()
    {
        $templateName = 'create';

        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.views.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'create.blade.php', $templateData);
        $this->commandData->commandInfo('create.blade.php created');
    }

    /**
     * Generate Update.
     *
     * @return void
     */
    private function generateUpdate()
    {
        $templateName = 'edit';

        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.views.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'edit.blade.php', $templateData);
        $this->commandData->commandInfo('edit.blade.php created');
    }

    /**
     * Generate Show Fields.
     *
     * @return void
     */
    private function generateShowFields()
    {
        $templateName = 'show_field';
        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_artomator_template('scaffold.views.'.$templateName);

        $fieldsStr = '';

        foreach ($this->commandData->fields as $field) {
            if (false === $field->inView) {
                continue;
            }
            $singleFieldStr = str_replace(
                '$FIELD_NAME_TITLE$',
                Str::title(str_replace('_', ' ', $field->name)),
                $fieldTemplate
            );
            $singleFieldStr = str_replace('$FIELD_NAME$', $field->name, $singleFieldStr);
            $singleFieldStr = fill_template($this->commandData->dynamicVars, $singleFieldStr);

            $fieldsStr .= $singleFieldStr."\n\n";
        }

        FileUtil::createFile($this->path, 'show_fields.blade.php', $fieldsStr);
        $this->commandData->commandInfo('show_fields.blade.php created');
    }

    /**
     * Generate Show.
     *
     * @return void
     */
    private function generateShow()
    {
        $templateName = 'show';

        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.views.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'show.blade.php', $templateData);
        $this->commandData->commandInfo('show.blade.php created');
    }

    /**
     * Rollback.
     *
     * @param array $views Views to rollback.
     *
     * @return void
     */
    public function rollback($views = [])
    {
        $files = [
            'table.blade.php',
            'index.blade.php',
            'fields.blade.php',
            'create.blade.php',
            'edit.blade.php',
            'show.blade.php',
            'show_fields.blade.php',
        ];

        if (false === empty($views)) {
            $files = [];
            foreach ($views as $view) {
                $files[] = $view.'.blade.php';
            }
        }

        if (true === $this->commandData->getAddOn('datatables')) {
            $files[] = 'datatables_actions.blade.php';
        }

        foreach ($files as $file) {
            if (true === $this->rollbackFile($this->path, $file)) {
                $this->commandData->commandComment($file.' file deleted');
            }
        }
    }
}
