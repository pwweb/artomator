<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLQueryGenerator extends BaseGenerator
{
    /**
     * @var CommandData
     */
    private $commandData;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileContents;

    /**
     * @var string
     */
    private $templateData;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->fileName = $commandData->config->pathGraphQL;
        $this->fileContents = file_get_contents($this->fileName);
        $this->templateData = get_artomator_template('graphql.query');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
    }

    public function generate()
    {
        if (Str::contains($this->fileContents, $this->templateData) === true) {
            $this->commandData->commandObj->info('GraphQL Query '.$this->commandData->config->mHumanPlural.' already exists; Skipping');

            return;
        }

        $this->fileContents = preg_replace('/(type Query {)(.+?[^}])(})/is', '$1$2'.$this->templateData.'$3', $this->fileContents);

        file_put_contents($this->fileName, $this->fileContents);

        $this->commandData->commandComment("\nGraphQL Query created");
    }

    public function rollback()
    {
        if (Str::contains($this->fileContents, $this->templateData)) {
            file_put_contents($this->fileName, str_replace($this->templateData, '', $this->fileContents));
            $this->commandData->commandComment('GraphQL Query deleted');
        }
    }
}
