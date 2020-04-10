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
        $model = $this->commandData->config->gHuman;

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
            if ($field->isFillable) {
                if ('foreignId' === $field->fieldType) {
                    continue;
                }
                $field_type = ucfirst($field->fieldType);
                $field_type .= (Str::contains($field->validations, 'required') ? '!' : '');

                $schema[] = $field->name.': '.$field_type;
            }
        }
        $schema = array_merge($schema, $this->generateRelations());

        return ['$SCHEMA$' => implode(infy_nl_tab(1, 1), $schema)];
    }

    private function generateRelations()
    {
        $relations = [];

        $count = 1;
        $fieldsArr = [];
        foreach ($this->commandData->relations as $relation) {
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            $relationShipText = $field;
            if (in_array($field, $fieldsArr)) {
                $relationShipText = $relationShipText.'_'.$count;
                $count++;
            }

            $relationText = $this->getRelationFunctionText($relation, $relationShipText);
            if (false === empty($relationText)) {
                $fieldsArr[] = $field;
                $relations[] = $relationText;
            }
        }

        return $relations;
    }

    protected function getRelationFunctionText($relationship, $relationText = null)
    {
        extract($this->prepareRelationship($relationship, $relationText));

        if (false === empty($functionName)) {
            return $this->generateRelation($functionName, $template);
        }

        return '';
    }

    private function generateRelation($functionName, $template)
    {
        $template = str_replace('$FUNCTION_NAME$', $functionName, $template);
        $template = str_replace('$RELATION_GRAPHQL_NAME$', ucfirst(Str::singular($functionName)), $template);

        return $template;
    }

    protected function prepareRelationship($relationship, $relationText = null)
    {
        $singularRelation = (false === empty($relationship->relationName)) ? $relationship->relationName : Str::camel(Str::singular($relationText));
        $pluralRelation = (false === empty($relationship->relationName)) ? $relationship->relationName : Str::camel(Str::plural($relationText));

        switch ($relationship->type) {
            case '1t1':
                $functionName = $singularRelation;
                $template = '$FUNCTION_NAME$: $RELATION_GRAPHQL_NAME$ @hasOne';
                break;
            case '1tm':
                $functionName = $pluralRelation;
                $template = '$FUNCTION_NAME$: [$RELATION_GRAPHQL_NAME$!]! @hasMany';
                break;
            case 'mt1':
                if (false === empty($relationship->relationName)) {
                    $singularRelation = $relationship->relationName;
                } elseif (isset($relationship->inputs[1])) {
                    $singularRelation = Str::camel(str_replace('_id', '', strtolower($relationship->inputs[1])));
                }
                $functionName = $singularRelation;
                $template = '$FUNCTION_NAME$: $RELATION_GRAPHQL_NAME$! @belongsTo';
                break;
            case 'mtm':
                $functionName = $pluralRelation;
                $template = '$FUNCTION_NAME$: [$RELATION_GRAPHQL_NAME$!]! @belongsToMany';
                break;
            case 'hmt':
                $functionName = $pluralRelation;
                $template = '';
                break;
            default:
                $functionName = '';
                $template = '';
                break;
        }

        return compact('functionName', 'template');
    }
}
