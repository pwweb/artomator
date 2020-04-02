<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLMutationGenerator extends BaseGenerator
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
        $this->templateData = get_artomator_template('graphql.mutations');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
        $this->templateData = fill_template($this->generateSchema(), $this->templateData);
    }

    public function generate()
    {
        if (true === Str::contains($this->fileContents, $this->templateData)) {
            $this->commandData->commandObj->info('GraphQL Mutations '.$this->commandData->config->mHumanPlural.' already exist; Skipping');

            return;
        }

        $this->fileContents = preg_replace('/(type Mutation {)(.+?[^}])(})/is', '$1$2'.str_replace('\\', '\\\\', $this->templateData).'$3', $this->fileContents);

        file_put_contents($this->fileName, $this->fileContents);

        $this->commandData->commandComment("\nGraphQL Mutations created");
    }

    public function rollback()
    {
        $strings = [
            'create',
            'update',
            'delete',
        ];
        $model = $this->commandData->config->mHuman;

        foreach ($strings as $string) {
            if (Str::contains($this->fileContents, $string.$model)) {
                $this->fileContents = preg_replace('/(\s)+('.$string.$model.'\()(.+?)(\):.+?)(\))/is', '', $this->fileContents);

                file_put_contents($this->fileName, $this->fileContents);
                $this->commandData->commandComment('GraphQL '.ucfirst($string).' Mutation deleted');
            }
        }
    }

    private function generateSchema()
    {
        $schema = [];
        foreach ($this->commandData->fields as $field) {
            if (true === in_array($field->name, ['created_at', 'updated_at', 'id'])) {
                continue;
            }
            $field_type = ucfirst($field->fieldType).(Str::contains($field->validations, 'required') ? '!' : '');

            $schema[] = $field->name.': '.$field_type;
        }

        return ['$SCHEMA$' => implode("\n\t\t", $schema)];
    }
}
