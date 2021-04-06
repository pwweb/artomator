<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLMutationGenerator extends BaseGenerator
{
    /**
     * Command Data.
     *
     * @var CommandData
     */
    private $commandData;

    /**
     * Filename
     *
     * @var string
     */
    private $fileName;

    /**
     * File Contents
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
        $this->templateData = get_artomator_template('graphql.mutations');
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
            $this->commandData->commandObj->info('GraphQL Mutations '.$this->commandData->config->mHumanPlural.' already exist; Skipping');

            return;
        }

        if (false === str::contains($this->fileContents, 'type Mutation {')) {
            $this->fileContents = preg_replace('/(type Query {)(.+?[^}])(})/is', '$1$2$3'."\n\ntype Mutation {  }", $this->fileContents);
        }

        $this->fileContents = preg_replace('/(type Mutation {)(.+?[^}])(})/is', '$1$2'.str_replace('\\', '\\\\', $this->templateData).'$3', $this->fileContents);

        file_put_contents($this->fileName, $this->fileContents);

        $this->commandData->commandComment("\nGraphQL Mutations created");
    }

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        $strings = [
            'create',
            'update',
            'delete',
        ];
        $model = $this->commandData->config->gHuman;

        foreach ($strings as $string) {
            if (true === Str::contains($this->fileContents, $string.$model)) {
                $this->fileContents = preg_replace('/(\s)+('.$string.$model.'\()(.+?)(\):.+?)(\))/is', '', $this->fileContents);

                file_put_contents($this->fileName, $this->fileContents);
                $this->commandData->commandComment('GraphQL '.ucfirst($string).' Mutation deleted');
            }
        }
    }
}
