<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLTypeGenerator extends BaseGenerator
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
        $this->filename = $commandData->config->pathGraphQL;
        $this->fileContents = file_get_contents($this->filename);
        $this->templateData = get_artomator_template('graphql.type');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
        $this->templateData = fill_template($this->generateSchema(), $this->templateData);
    }

    public function generate()
    {
        if (Str::contains($this->fileContents, $this->templateData) === true) {
            $this->commandData->commandObj->info('GraphQL Type '.$this->commandData->config->mHumanPlural.' already exists; Skipping');

            return;
        }

        $this->fileContents .= "\n".$this->templateData;
        file_put_contents($this->filename, $this->fileContents);

        $this->commandData->commandComment("\nGraphQL Type created");
    }

    public function rollback()
    {
        if (Str::contains($this->fileContents, $this->templateData)) {
            file_put_contents($this->path, str_replace($this->templateData, '', $this->fileContents));
            $this->commandData->commandComment('GraphQL Type deleted');
        }
    }

    private function generateSchema()
    {
        $schema = [];
        foreach ($this->commandData->fields as $field) {
            if (true === in_array($field->name, ['id'])) {
                continue;
            }
            $field_type = ucfirst($field->fieldType).(Str::contains($field->validations, 'required') ? '!' : '');

            $schema[] = $field->name.': '.$field_type;
        }

        return ['$SCHEMA$' => implode(infy_nl_tab(1, 1), $schema)];
    }
}
