<?php

namespace PWWEB\Artomator\Generators\API;

use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\BaseGenerator;
use PWWEB\Artomator\Utils\FileUtil;

class APITypeGenerator extends BaseGenerator
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
        $this->path = $commandData->config->pathApiType;
        $this->fileName = $this->commandData->modelName.'ApiType.php';
    }

    public function generate()
    {
        $templateData = get_template('api.type.api_type', 'artomator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandObj->comment("\nApiType created: ");
        $this->commandData->commandObj->info($this->fileName);
    }

    private function fillDocs($templateData)
    {
        $methods = ['type'];

        if ($this->commandData->getAddOn('swagger')) {
            $templatePrefix = 'type_docs';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'api.docs.type';
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
            $this->commandData->commandComment('API Type file deleted: '.$this->fileName);
        }
    }
}
