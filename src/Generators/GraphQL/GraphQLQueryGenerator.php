<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use PWWEB\Artomator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Utils\FileUtil;

class GraphQLQueryGenerator extends BaseGenerator
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
        $this->path = $commandData->config->pathGraphQLQuery;
        $this->fileName = $this->commandData->modelName.'Query.php';
    }

    public function generate()
    {
        $templateName = 'graphql_query';

        $templateData = get_template("graphql.query.$templateName", 'artomator');
        $templateData = str_replace('$ARGUMENTS$', $this->generateArguments(), $templateData);
        $templateData = str_replace('$RESOLVES$', $this->generateResolves(), $templateData);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);


        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nGraphQL Query created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function generateArguments()
    {
        $arguments = [];
        foreach ($this->commandData->fields as $field) {
            if (in_array($field->name, ['created_at','updated_at','id']) === true) {
                continue;
            }

            $arguments[] = "'" . $field->name . "' => ['name' => '" . $field->name . "', 'type' => Type::" . $field->fieldType . "()],";
        }

        return implode(arty_nl_tab(1, 3), $arguments);
    }

    private function generateResolves()
    {
        $resolves = [];
        foreach ($this->commandData->fields as $field) {
            if (in_array($field->name, ['created_at','updated_at','id']) === true) {
                continue;
            }

            $resolves[] = "if (isset(\$args['" . $field->name . "']) === true)\n\t\t{\n\t\t\treturn \$MODEL_NAME\$::where('" . $field->name . "', \$args['" . $field->name . "'])->get();\n\t\t}\n";
        }

        return implode(arty_nl_tab(1, 2), $resolves);
    }

    private function fillDocs($templateData)
    {
        $methods = ['query'];

        if ($this->commandData->getAddOn('swagger')) {
            $templatePrefix = 'query_docs';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'graphql.docs.query';
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
            $this->commandData->commandComment('GraphQL Query file deleted: '.$this->fileName);
        }
    }
}
