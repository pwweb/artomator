<?php

namespace PWWEB\Artomator\Generators\Scaffold;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\ViewServiceProviderGenerator;
use PWWEB\Artomator\Utils\VueFieldGenerator;

class VueGenerator extends BaseGenerator
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
     * Html Fields.
     *
     * @var array
     */
    private $htmlFields;

    /**
     * Construct function.
     *
     * @param CommandData $commandData Command Data.
     *
     * @return void
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathVues;
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
    }

    /**
     * Generate Function.
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
            $this->commandData->addDynamicVariable('$FILES$', ' enctype="multipart/form-data"');
        }

        $this->commandData->commandComment("\nGenerating Vues...");

        if (true === $this->commandData->getOption('views')) {
            $vuesToBeGenerated = explode(',', $this->commandData->getOption('views'));

            if (true === in_array('index', $vuesToBeGenerated)) {
                $this->generateIndex();
            }

            if (count(array_intersect(['create', 'update'], $vuesToBeGenerated)) > 0) {
                $this->generateFields();
            }

            if (true === in_array('create', $vuesToBeGenerated)) {
                $this->generateCreate();
            }

            if (true === in_array('edit', $vuesToBeGenerated)) {
                $this->generateUpdate();
            }

            if (true === in_array('show', $vuesToBeGenerated)) {
                $this->generateShowFields();
                $this->generateShow();
            }
        } else {
            $this->generateIndex();
            $this->generateFields();
            $this->generateCreate();
            $this->generateUpdate();
            $this->generateShowFields();
            $this->generateShow();
        }

        $this->commandData->commandComment('Vues created: ');
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

        return $templateData;
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

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELD_HEADERS$', $this->generateTableHeaderFields(), $templateData);

        $cellFieldTemplate = get_artomator_template('scaffold.vues.table_cell');

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

        $headerFieldTemplate = get_artomator_template('scaffold.vues.'.$templateName);

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

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        // if (true === $this->commandData->getAddOn('datatables')) {
        //     $templateData = str_replace('$PAGINATE$', '', $templateData);
        // } else {
        //     $paginate = $this->commandData->getOption('paginate');

        //     if (true === $paginate) {
        //         $paginateTemplate = get_artomator_template('scaffold.vues.paginate');

        //         $paginateTemplate = fill_template($this->commandData->dynamicVars, $paginateTemplate);

        //         $templateData = str_replace('$PAGINATE$', $paginateTemplate, $templateData);
        //     } else {
        //         $templateData = str_replace('$PAGINATE$', '', $templateData);
        //     }
        // }

        $templateData = str_replace('$TABLE$', $this->generateTable(), $templateData);

        FileUtil::createFile($this->path, 'Index.vue', $templateData);

        $this->commandData->commandInfo('Index.vue created');
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
        $createForm = [];
        $editForm = [];

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
                    $required = ' required';
                }

                $size = " $sizeText=\"$sizeInNumber\"";
                $minMaxRules .= $size;
            }

            $this->commandData->addDynamicVariable('$SIZE$', $minMaxRules);

            $this->commandData->addDynamicVariable('$REQUIRED$', $required);

            $fieldTemplate = VueFieldGenerator::generateHTML($field, $this->templateType, $localized);

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
                $vuePath = $this->commandData->config->prefixes['vue'];
                if (false === empty($vuePath)) {
                    $tableName = $vuePath.'.'.$tableName;
                }

                $variableName = Str::singular($selectTable).'Items';
                // e.g $userItems

                $fieldTemplate = $this->generateVueComposer($tableName, $variableName, $columns, $selectTable, $modalName);
            }

            if (false === empty($fieldTemplate)) {
                $fieldTemplate = fill_template_with_field_data(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $fieldTemplate,
                    $field
                );
                $this->htmlFields[] = $fieldTemplate;
                $createForm[] = $field.': null,';
                $editForm[] = $field.': props.$MODEL_NAME_CAMEL$.'.$field.',';
            }
        }

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode("\n\n", $this->htmlFields), $templateData);

        $this->commandData->addDynamicVariable('$CREATE_DATA$', implode('\n', $createForm));
        $this->commandData->addDynamicVariable('$EDIT_DATA$', implode('\n', $editForm));

        FileUtil::createFile($this->path, 'Fields.vue', $templateData);
        $this->commandData->commandInfo('field.vue created');
    }

    /**
     * Generate Vue Composer.
     *
     * @param string      $tableName    Table Name.
     * @param string      $variableName Variable Name.
     * @param array       $columns      Columns.
     * @param string      $selectTable  Select Table.
     * @param string|null $modelName    Model Name.
     *
     * @return void
     */
    private function generateVueComposer($tableName, $variableName, $columns, $selectTable, $modelName = null)
    {
        $templateName = 'scaffold.fields.select';
        if (true === $this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_artomator_template($templateName);

        //TODO: Confirm if this is required still.
        // $vueServiceProvider = new ViewServiceProviderGenerator($this->commandData);
        // $vueServiceProvider->generate();
        // $vueServiceProvider->addViewVariables($tableName.'.fields', $variableName, $columns, $selectTable, $modelName);

        $fieldTemplate = str_replace(
            '$INPUT_ARR$',
            $variableName,
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

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'Create.vue', $templateData);
        $this->commandData->commandInfo('Create.vue created');
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

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'Edit.vue', $templateData);
        $this->commandData->commandInfo('Edit.vue created');
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
        $fieldTemplate = get_artomator_template('scaffold.vues.'.$templateName);

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
        $templateName = 'show_fields';
        $fieldTemplate = get_artomator_template('scaffold.vues.'.$templateName);

        $fieldTemplate = str_replace('$FIELDS$', $fieldsStr, $fieldTemplate);

        FileUtil::createFile($this->path, 'Show_fields.vue', $fieldTemplate);
        $this->commandData->commandInfo('Show_fields.vue created');
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

        $templateData = get_artomator_template('scaffold.vues.'.$templateName);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'Show.vue', $templateData);
        $this->commandData->commandInfo('Show.vue created');
    }

    /**
     * Rollback Function.
     *
     * @param array $vues Vue views to rollback.
     *
     * @return void
     */
    public function rollback($vues = [])
    {
        $files = [
            'Table.vue',
            'Index.vue',
            'Fields.vue',
            'Create.vue',
            'Edit.vue',
            'Show.vue',
            'Show_fields.vue',
        ];

        if (false === empty($vues)) {
            $files = [];
            foreach ($vues as $vue) {
                $files[] = $vue.'.vue';
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
