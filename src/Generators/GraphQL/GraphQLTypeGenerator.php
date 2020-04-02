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
        $this->fileName = $commandData->config->pathGraphQL;
        $this->fileContents = file_get_contents($this->fileName);
        $this->templateData = get_artomator_template('graphql.type');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
        $this->templateData = fill_template($this->generateSchema(), $this->templateData);
    }

    public function generate()
    {
        if (true === Str::contains($this->fileContents, $this->templateData)) {
            $this->commandData->commandObj->info('GraphQL Type '.$this->commandData->config->mHumanPlural.' already exists; Skipping');

            return;
        }

        $this->fileContents .= "\n".$this->templateData;
        file_put_contents($this->fileName, $this->fileContents);

        $this->commandData->commandComment("\nGraphQL Type created");
    }

    public function rollback()
    {
        $model = $this->commandData->config->mHuman;

        if (Str::contains($this->fileContents, 'type '.$model)) {
            $this->fileContents = preg_replace('/(\s)+(type '.$model.')(.+?)(})/is', '', $this->fileContents);

            file_put_contents($this->fileName, $this->fileContents);
            $this->commandData->commandComment('GraphQL Type deleted');
        }
    }

    private function generateSchema()
    {
        $schema = [];
        foreach ($this->commandData->fields as $field) {
            if (true === in_array($field->name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            $field_type = ucfirst($field->fieldType).(Str::contains($field->validations, 'required') ? '!' : '');

            $schema[] = $field->name.': '.$field_type;
        }

        return ['$SCHEMA$' => implode(infy_nl_tab(1, 1), $schema)];
    }
}
