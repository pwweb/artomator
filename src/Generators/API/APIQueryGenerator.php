<?php

namespace PWWEB\Artomator\Generators\API;

use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\BaseGenerator;
use PWWEB\Artomator\Utils\FileUtil;

class APIQueryGenerator extends BaseGenerator
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
        $this->path = $commandData->config->pathApiQuery;
        $this->fileName = $this->commandData->modelName.'Query.php';
    }

    public function generate()
    {
        if ($this->commandData->getOption('repositoryPattern')) {
            $templateName = 'api_query';
        } else {
            $templateName = 'model_api_query';
        }

        $templateData = get_template("api.query.$templateName", 'artomator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nAPI Query created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillDocs($templateData)
    {
        $methods = ['query', 'index', 'store', 'show', 'update', 'destroy'];

        if ($this->commandData->getAddOn('swagger')) {
            $templatePrefix = 'query_docs';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'api.docs.query';
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
            $this->commandData->commandComment('API Query file deleted: '.$this->fileName);
        }
    }
}
