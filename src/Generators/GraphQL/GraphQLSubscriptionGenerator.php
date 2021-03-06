<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLSubscriptionGenerator extends BaseGenerator
{
    /**
     * Command Data.
     *
     * @var CommandData
     */
    private $commandData;

    /**
     * Filename.
     *
     * @var string
     */
    private $fileName;

    /**
     * File Contents.
     *
     * @var string
     */
    private $fileContents;

    /**
     * Template Data.
     *
     * @var string
     */
    private $templateData;

    /**
     * Constructor.
     *
     * @param CommandData $commandData Command Data.
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->fileName = $commandData->config->pathGraphQL;
        $this->fileContents = file_get_contents($this->fileName);
        $this->templateData = get_artomator_template('graphql.subscription');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
    }

    /**
     * Generate.
     *
     * @return void
     */
    public function generate()
    {
        if (true === Str::contains($this->fileContents, $this->templateData)) {
            $this->commandData->commandObj->info('GraphQL Subscription '.$this->commandData->config->mHumanPlural.' already exists; Skipping');

            return;
        }

        $this->fileContents = preg_replace('/(type Subscription {)(.+?[^}])(})/is', '$1$2'.$this->templateData.'$3', $this->fileContents);

        file_put_contents($this->fileName, $this->fileContents);

        $this->commandData->commandComment("\nGraphQL Subscription created");
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        if (true === Str::contains($this->fileContents, $this->templateData)) {
            file_put_contents($this->fileName, str_replace($this->templateData, '', $this->fileContents));
            $this->commandData->commandComment('GraphQL Subscription deleted');
        }
    }
}
