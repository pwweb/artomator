<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use PWWEB\Artomator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Utils\FileUtil;

class GraphQLTypeGenerator extends BaseGenerator
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
    private $fileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathGraphQLType;
        $this->fileName = $this->commandData->modelName.'Type.php';
    }

    public function generate()
    {
        $templateData = get_template('graphql.type.graphql_type', 'artomator');
        $templateData = str_replace('$SCHEMA$', $this->generateSchema(), $templateData);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandObj->comment("\nGraphQL Type created: ");
        $this->commandData->commandObj->info($this->fileName);
    }

    private function generateSchema()
    {
        $schema = [];
        foreach ($this->commandData->fields as $field) {
            if (in_array($field->name, ['created_at','updated_at','id']) === true) {
                continue;
            }
            if ($field->isNotNull === true) {
                $field_type = "Type::nonNull(Type::" . $field->fieldType . "())";
            } else {
                $field_type = "Type::" . $field->fieldType . "()";
            }

            $schema[] = "'" . $field->name . "' => [" . arty_nl_tab(1, 4) . "'type' => " . $field_type . "," . arty_nl_tab(1, 4) . "'description' => 'The " . $field->name . " of the model'" . arty_nl_tab(1, 3) . "],";
        }

        return implode(arty_nl_tab(1, 3), $schema);
    }

    private function fillDocs($templateData)
    {
        $methods = ['type'];

        if ($this->commandData->getAddOn('swagger')) {
            $templatePrefix = 'type_docs';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'graphql.docs.type';
            $templateType = 'artomator';
        }

        foreach ($methods as $method) {
            $key = '$DOC_'.strtoupper($method).'$';
            $docTemplate = get_template($templatePrefix.'.'.$method, $templateType);
            $docTemplate = fill_template($this->commandData->dynamicVars, $docTemplate);
            $templateData = str_replace($key, $docTemplate, $templateData);
        }

        return $templateData;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('GraphQL Type file deleted: '.$this->fileName);
        }
    }
}
