<?php

namespace PWWEB\Artomator\Utils;

use InfyOm\Generator\Common\GeneratorField;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;

class VueFieldGenerator
{
    /**
     * Generate HTML.
     *
     * @param GeneratorField $field        Field.
     * @param string         $templateType Template type.
     * @param bool        $localized    Localized.
     *
     * @return string
     */
    public static function generateHTML(GeneratorField $field, $templateType, $localized = false)
    {
        $fieldTemplate = '';

        $localized = (true === $localized) ? '_locale' : '';
        switch ($field->htmlType) {
            case 'text':
            case 'textarea':
            case 'date':
            case 'file':
            case 'email':
            case 'password':
            case 'number':
                $fieldTemplate = get_template('scaffold.vues.fields.'.$field->htmlType.$localized, $templateType);

                break;
            case 'select':
            case 'enum':
                $fieldTemplate = get_template('scaffold.vues.fields.select'.$localized, $templateType);
                $radioLabels = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($field->htmlValues);

                $fieldTemplate = str_replace(
                    '$INPUT_ARR$',
                    GeneratorFieldsInputUtil::prepareKeyValueArrayStr($radioLabels),
                    $fieldTemplate
                );

                break;
            case 'checkbox':
                $fieldTemplate = get_template('scaffold.vues.fields.checkbox'.$localized, $templateType);
                if (count($field->htmlValues) > 0) {
                    $checkboxValue = $field->htmlValues[0];
                } else {
                    $checkboxValue = 1;
                }
                $fieldTemplate = str_replace('$CHECKBOX_VALUE$', $checkboxValue, $fieldTemplate);

                break;
            case 'radio':
                $fieldTemplate = get_template('scaffold.vues.fields.radio_group'.$localized, $templateType);
                $radioTemplate = get_template('scaffold.vues.fields.radio'.$localized, $templateType);

                $radioLabels = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($field->htmlValues);

                $radioButtons = [];
                foreach ($radioLabels as $label => $value) {
                    $radioButtonTemplate = str_replace('$LABEL$', $label, $radioTemplate);
                    $radioButtonTemplate = str_replace('$VALUE$', $value, $radioButtonTemplate);
                    $radioButtons[] = $radioButtonTemplate;
                }
                $fieldTemplate = str_replace('$RADIO_BUTTONS$', implode("\n", $radioButtons), $fieldTemplate);

                break;
            case 'toggle-switch':
                $fieldTemplate = get_template('scaffold.vues.fields.toggle-switch'.$localized, $templateType);

                break;
        }

        return $fieldTemplate;
    }
}
