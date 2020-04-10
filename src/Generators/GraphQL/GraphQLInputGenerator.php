<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLInputGenerator extends BaseGenerator
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
        $this->templateData = get_artomator_template('graphql.inputs');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
        $this->templateData = fill_template($this->generateSchema(), $this->templateData);
    }

    public function generate()
    {
        if (true === Str::contains($this->fileContents, $this->templateData)) {
            $this->commandData->commandObj->info('GraphQL Inputs '.$this->commandData->config->mHumanPlural.' already exist; Skipping');

            return;
        }

        $this->fileContents .= $this->templateData;

        file_put_contents($this->fileName, $this->fileContents);

        $this->commandData->commandComment("\nGraphQL Inputs created");
    }

    public function rollback()
    {
        if (Str::contains($this->fileContents, $this->templateData)) {
            file_put_contents($this->fileName, str_replace($this->templateData, '', $this->fileContents));
            $this->commandData->commandComment('GraphQL Inputs deleted');
        }
    }

    private function generateSchema()
    {
        $schema = [];
        foreach ($this->commandData->fields as $field) {
            if ($field->isFillable) {
                if ('foreignId' === $field->fieldType) {
                    continue;
                } else {
                    $field_type = ucfirst($field->fieldType);
                }

                $field_type .= (Str::contains($field->validations, 'required') ? '!' : '');

                $schema[] = $field->name.': '.$field_type;
            }
        }
        $schema = array_merge($schema, $this->generateRelations());
        $schema = implode(infy_nl_tab(1, 1), $schema);
        $create_schema = str_replace('$TYPE$', 'Create', $schema);
        $update_schema = str_replace('$TYPE$', 'Update', $schema);
        $upsert_schema = str_replace('$TYPE$', 'Upsert', $schema);

        return [
            '$CREATE_SCHEMA$' => $create_schema,
            '$UPDATE_SCHEMA$' => $update_schema,
            '$UPSERT_SCHEMA$' => $upsert_schema
        ];
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

            $relationText = $this->getRelationFunctionText($relationShipText, $relation);
            if (false === empty($relationText)) {
                $fieldsArr[] = $field;
                $relations[] = $relationText;
            }
        }

        return $relations;
    }

    protected function getRelationFunctionText($relationText = null, $relationship)
    {
        extract($this->prepareRelationship($relationText, $relationship));

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

    protected function prepareRelationship($relationText = null, $relationship)
    {
        $singularRelation = (false === empty($relationship->relationName)) ? $relationship->relationName : Str::camel(Str::singular($relationText));
        $pluralRelation = (false === empty($relationship->relationName)) ? $relationship->relationName : Str::camel(Str::plural($relationText));

        switch ($relationship->type) {
            case '1t1':
                $functionName = $singularRelation;
                $template = '$FUNCTION_NAME$: $TYPE$$RELATION_GRAPHQL_NAME$';
                $templateFile = '';
                break;
            case '1tm':
                $functionName = $pluralRelation;
                $template = '$FUNCTION_NAME$: $TYPE$$RELATION_GRAPHQL_NAME$HasMany';
                $templateFile = 'hasMany';
                break;
            case 'mt1':
                if (false === empty($relationship->relationName)) {
                    $singularRelation = $relationship->relationName;
                } elseif (isset($relationship->inputs[1])) {
                    $singularRelation = Str::camel(str_replace('_id', '', strtolower($relationship->inputs[1])));
                }
                $functionName = $singularRelation;
                $template = '$FUNCTION_NAME$: $TYPE$$RELATION_GRAPHQL_NAME$BelongsTo';
                $templateFile = 'belongsTo';
                break;
            case 'mtm':
                $functionName = $pluralRelation;
                $template = '$FUNCTION_NAME$: $TYPE$$RELATION_GRAPHQL_NAME$BelongsToMany';
                $templateFile = 'belongsToMany';
                break;
            case 'hmt':
                $functionName = $pluralRelation;
                $template = '';
                $templateFile = '';
                break;
            default:
                $functionName = '';
                $template = '';
                $templateFile = '';
                break;
        }
        if (false === empty($templateFile)) {
            $templateFile = get_artomator_template('graphql.relations.'.$templateFile);
            $templateFile = str_replace('$RELATION_GRAPHQL_NAME$', ucfirst(Str::singular($functionName)), $templateFile);

            if (false === Str::contains($this->fileContents, $templateFile) && false === Str::contains($this->templateData, $templateFile)) {
                $this->templateData .= $templateFile;
            }
        }

        return compact('functionName', 'template');
    }
}
