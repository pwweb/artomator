<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLInputGenerator extends BaseGenerator
{
    /**
     * Command data.
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
     * File contents.
     *
     * @var string
     */
    private $fileContents;

    /**
     * Template data.
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
        $this->templateData = get_artomator_template('graphql.inputs');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
        $this->templateData = fill_template($this->generateSchema(), $this->templateData);
    }

    /**
     * Generate Command.
     *
     * @return void
     */
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

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        if (true === Str::contains($this->fileContents, $this->templateData)) {
            file_put_contents($this->fileName, str_replace($this->templateData, '', $this->fileContents));
            $this->commandData->commandComment('GraphQL Inputs deleted');
        }
    }

    /**
     * Sanitise the field types.
     *
     * @param string $fieldType Field type.
     *
     * @return void
     */
    private function sanitiseFieldTypes(string $fieldType)
    {
        $needle = "/\(.+\)?/";
        $replace = '';
        $fieldType = preg_replace($needle, $replace, $fieldType);
        // There are 5 basic scalar types + 2 lighthouse ones (Date and DateTime);
        switch ($fieldType) {
            case 'bigIncrements':
            case 'bigInteger':
            case 'binary':
            case 'increments':
            case 'integer':
            case 'mediumIncrements':
            case 'mediumInteger':
            case 'smallIncrements':
            case 'smallInteger':
            case 'tinyIncrements':
            case 'tinyInteger':
            case 'unsignedBigInteger':
            case 'unsignedInteger':
            case 'unsignedMediumInteger':
            case 'unsignedSmallInteger':
            case 'unsignedTinyInteger':
            case 'year':
                return 'Int';

            case 'unsignedDecimal':
            case 'point':
            case 'polygon':
            case 'multiPoint':
            case 'multiPolygon':
            case 'float':
            case 'decimal':
            case 'double':
                return 'Float';

            case 'uuid':
            case 'string':
            case 'text':
            case 'rememberToken':
            case 'mediumText':
            case 'multiLineString':
            case 'ipAddress':
            case 'json':
            case 'jsonb':
            case 'lineString':
            case 'longText':
            case 'macAddress':
            case 'char':
                return 'String';

            case 'boolean':
                return 'Boolean';

            case 'foreignId':
                return 'ID';

            case 'time':
            case 'timeTz':
            case 'timestamp':
            case 'timestampTz':
            case 'timestamps':
            case 'timestampsTz':
            case 'softDeletes':
            case 'softDeletesTz':
            case 'nullableTimestamps':
            case 'dateTime':
            case 'dateTimeTz':
                return 'DateTime';

            case 'date':
                return 'Date';

            case 'set':
            case 'nullableMorphs':
            case 'nullableUuidMorphs':
            case 'morphs':
            case 'uuidMorphs':
            case 'geometry':
            case 'geometryCollection':
            case 'enum':
            default:
                return ucfirst($fieldType);
        }
    }

    /**
     * Generate Schema.
     *
     * @return void
     */
    private function generateSchema()
    {
        $schema = [];
        foreach ($this->commandData->fields as $field) {
            if (true === $field->isFillable) {
                if ('foreignId' === $field->fieldType) {
                    continue;
                } else {
                    $field_type = $this->sanitiseFieldTypes($field->fieldType);
                }

                $field_type .= ((true === Str::contains($field->validations, 'required')) ? '!' : '');

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
            '$UPDATE_SCHEMA$' => str_replace('!', '', $update_schema),
            '$UPSERT_SCHEMA$' => str_replace('!', '', $upsert_schema),
        ];
    }

    /**
     * Generate Relations.
     *
     * @return void
     */
    private function generateRelations()
    {
        $relations = [];

        $count = 1;
        $fieldsArr = [];
        foreach ($this->commandData->relations as $relation) {
            $field = (true === isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            $relationShipText = $field;
            if (true === in_array($field, $fieldsArr)) {
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

    /**
     * Get the relation function tex.t.
     *
     * @param string      $relationship Relationship type.
     * @param string|null $relationText Relationship text.
     *
     * @return void
     */
    protected function getRelationFunctionText($relationship, $relationText = null)
    {
        extract($this->prepareRelationship($relationship, $relationText));

        if (false === empty($functionName)) {
            return $this->generateRelation($functionName, $template);
        }

        return '';
    }

    /**
     * Generate Relation.
     *
     * @param string $functionName Function name.
     * @param string $template     Template text.
     *
     * @return void
     */
    private function generateRelation($functionName, $template)
    {
        $template = str_replace('$FUNCTION_NAME$', $functionName, $template);
        $template = str_replace('$RELATION_GRAPHQL_NAME$', ucfirst(Str::singular($functionName)), $template);

        return $template;
    }

    /**
     * Prepare Relationship.
     *
     * @param Model       $relationship Relationship.
     * @param string|null $relationText Relation Text.
     *
     * @return array
     */
    protected function prepareRelationship($relationship, $relationText = null)
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
                } elseif (true === isset($relationship->inputs[1])) {
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
