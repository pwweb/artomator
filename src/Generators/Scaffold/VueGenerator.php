<?php

namespace PWWEB\Artomator\Generators\Scaffold;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use PWWEB\Artomator\Utils\VueFieldGenerator;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\VueServiceProviderGenerator;

class VueGenerator extends BaseGenerator
{
    /**
     * @var CommandData
     */
    private $commandData;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $templateType;

    /**
     * @var array
     */
    private $htmlFields;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathVues;
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
    }

    public function generate()
    {
        if (! file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $htmlInputs = Arr::pluck($this->commandData->fields, 'htmlInput');
        if (in_array('file', $htmlInputs)) {
            $this->commandData->addDynamicVariable('$FILES$', ", 'files' => true");
        }

        $this->commandData->commandComment("\nGenerating Vues...");

        if ($this->commandData->getOption('views')) {
            $vuesToBeGenerated = explode(',', $this->commandData->getOption('views'));

            if (in_array('index', $vuesToBeGenerated)) {
                $this->generateTable();
                $this->generateIndex();
            }

            if (count(array_intersect(['create', 'update'], $vuesToBeGenerated)) > 0) {
                $this->generateFields();
            }

            if (in_array('create', $vuesToBeGenerated)) {
                $this->generateCreate();
            }

            if (in_array('edit', $vuesToBeGenerated)) {
                $this->generateUpdate();
            }

            if (in_array('show', $vuesToBeGenerated)) {
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

        $this->commandData->commandComment('Vues created: ');
    }

    private function generateTable()
    {
        if ($this->commandData->getAddOn('datatables')) {
            $templateData = $this->generateDataTableBody();
            $this->generateDataTableActions();
        } else {
            $templateData = $this->generateBladeTableBody();
        }

        FileUtil::createFile($this->path, 'table.vue', $templateData);

        $this->commandData->commandInfo('table.vue created');
    }

    private function generateDataTableBody()
    {
        $templateData = get_artomator_template('scaffold.vues.datatable_body');

        return fill_template($this->commandData->dynamicVars, $templateData);
    }

    private function generateDataTableActions()
    {
        $templateName = 'datatables_actions';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'datatables_actions.vue', $templateData);

        $this->commandData->commandInfo('datatables_actions.vue created');
    }

    private function generateBladeTableBody()
    {
        $templateName = 'blade_table_body';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELD_HEADERS$', $this->generateTableHeaderFields(), $templateData);

        $cellFieldTemplate = get_artomator_template('scaffold.vues.table_cell');

        $tableBodyFields = [];

        foreach ($this->commandData->fields as $field) {
            if (! $field->inIndex) {
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

    private function generateTableHeaderFields()
    {
        $templateName = 'table_header';

        $localized = false;
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
            $localized = true;
        }

        $headerFieldTemplate = get_artomator_template('scaffold.vues.'.$templateName);

        $headerFields = [];

        foreach ($this->commandData->fields as $field) {
            if (! $field->inIndex) {
                continue;
            }

            if ($localized) {
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

    private function generateIndex()
    {
        $templateName = 'index';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        if ($this->commandData->getAddOn('datatables')) {
            $templateData = str_replace('$PAGINATE$', '', $templateData);
        } else {
            $paginate = $this->commandData->getOption('paginate');

            if ($paginate) {
                $paginateTemplate = get_artomator_template('scaffold.vues.paginate');

                $paginateTemplate = fill_template($this->commandData->dynamicVars, $paginateTemplate);

                $templateData = str_replace('$PAGINATE$', $paginateTemplate, $templateData);
            } else {
                $templateData = str_replace('$PAGINATE$', '', $templateData);
            }
        }

        FileUtil::createFile($this->path, 'index.vue', $templateData);

        $this->commandData->commandInfo('index.vue created');
    }

    private function generateFields()
    {
        $templateName = 'fields';

        $localized = false;
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
            $localized = true;
        }

        $this->htmlFields = [];

        foreach ($this->commandData->fields as $field) {
            if (! $field->inForm) {
                continue;
            }

            $validations = explode('|', $field->validations);
            $minMaxRules = '';
            $required = '';
            foreach ($validations as $validation) {
                if (! Str::contains($validation, ['max:', 'min:'])) {
                    continue;
                }

                $validationText = substr($validation, 0, 3);
                $sizeInNumber = substr($validation, 4);

                $sizeText = ('min' == $validationText) ? 'minlength' : 'maxlength';
                if ('number' == $field->htmlType) {
                    $sizeText = $validationText;
                }

                if (Str::contains($validation, 'required')) {
                    $required = ',\'required\' => true';
                }

                $size = ",'$sizeText' => $sizeInNumber";
                $minMaxRules .= $size;
            }

            $this->commandData->addDynamicVariable('$SIZE$', $minMaxRules);

            $this->commandData->addDynamicVariable('$REQUIRED$', $required);

            $fieldTemplate = VueFieldGenerator::generateHTML($field, $this->templateType, $localized);

            if ('selectTable' == $field->htmlType) {
                $inputArr = explode(',', $field->htmlValues[1]);
                $columns = '';
                foreach ($inputArr as $item) {
                    $columns .= "'$item'".',';  //e.g 'email,id,'
                }
                $columns = substr_replace($columns, '', -1); // remove last ,

                $htmlValues = explode(',', $field->htmlValues[0]);
                $selectTable = $htmlValues[0];
                $modalName = null;
                if (2 == count($htmlValues)) {
                    $modalName = $htmlValues[1];
                }

                $tableName = $this->commandData->config->tableName;
                $vuePath = $this->commandData->config->prefixes['vue'];
                if (! empty($vuePath)) {
                    $tableName = $vuePath.'.'.$tableName;
                }

                $variableName = Str::singular($selectTable).'Items'; // e.g $userItems

                $fieldTemplate = $this->generateVueComposer($tableName, $variableName, $columns, $selectTable, $modalName);
            }

            if (! empty($fieldTemplate)) {
                $fieldTemplate = fill_template_with_field_data(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $fieldTemplate,
                    $field
                );
                $this->htmlFields[] = $fieldTemplate;
            }
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode("\n\n", $this->htmlFields), $templateData);

        FileUtil::createFile($this->path, 'fields.vue', $templateData);
        $this->commandData->commandInfo('field.vue created');
    }

    private function generateVueComposer($tableName, $variableName, $columns, $selectTable, $modelName = null)
    {
        $templateName = 'scaffold.fields.select';
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_artomator_template($templateName);

        $vueServiceProvider = new VueServiceProviderGenerator($this->commandData);
        $vueServiceProvider->generate();
        $vueServiceProvider->addViewVariables($tableName.'.fields', $variableName, $columns, $selectTable, $modelName);

        $fieldTemplate = str_replace(
            '$INPUT_ARR$',
            '$'.$variableName,
            $fieldTemplate
        );

        return $fieldTemplate;
    }

    private function generateCreate()
    {
        $templateName = 'create';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'create.vue', $templateData);
        $this->commandData->commandInfo('create.vue created');
    }

    private function generateUpdate()
    {
        $templateName = 'edit';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'edit.vue', $templateData);
        $this->commandData->commandInfo('edit.vue created');
    }

    private function generateShowFields()
    {
        $templateName = 'show_field';
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_artomator_template('scaffold.vues.'.$templateName);

        $fieldsStr = '';

        foreach ($this->commandData->fields as $field) {
            if (! $field->inVue) {
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

        FileUtil::createFile($this->path, 'show_fields.vue', $fieldsStr);
        $this->commandData->commandInfo('show_fields.vue created');
    }

    private function generateShow()
    {
        $templateName = 'show';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'show.vue', $templateData);
        $this->commandData->commandInfo('show.vue created');
    }

    public function rollback($vues = [])
    {
        $files = [
            'table.vue',
            'index.vue',
            'fields.vue',
            'create.vue',
            'edit.vue',
            'show.vue',
            'show_fields.vue',
        ];

        if (! empty($vues)) {
            $files = [];
            foreach ($vues as $vue) {
                $files[] = $vue.'.vue';
            }
        }

        if ($this->commandData->getAddOn('datatables')) {
            $files[] = 'datatables_actions.vue';
        }

        foreach ($files as $file) {
            if ($this->rollbackFile($this->path, $file)) {
                $this->commandData->commandComment($file.' file deleted');
            }
        }
    }
}
