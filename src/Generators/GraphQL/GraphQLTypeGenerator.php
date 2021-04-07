<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

class GraphQLTypeGenerator extends BaseGenerator
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
        $this->templateData = get_artomator_template('graphql.type');
        $this->templateData = fill_template($this->commandData->dynamicVars, $this->templateData);
        $this->templateData = fill_template($this->generateSchema(), $this->templateData);
    }

    /**
     * Generate.
     *
     * @return void
     */
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

    /**
     * Rollback.
     *
     * @return void
     */
    public function rollback()
    {
        $model = $this->commandData->config->gHuman;

        if (true === Str::contains($this->fileContents, 'type '.$model)) {
            $this->fileContents = preg_replace('/(\s)+(type '.$model.')(.+?)(})/is', '', $this->fileContents);

            file_put_contents($this->fileName, $this->fileContents);
            $this->commandData->commandComment('GraphQL Type deleted');
        }
    }

    /**
     * Generate Schema.
     *
     * @return array
     */
    private function generateSchema()
    {
        $schema = [];
        foreach ($this->commandData->fields as $field) {
            if (true === $field->isFillable) {
                if ('foreignId' === $field->fieldType) {
                    continue;
                }
                $field_type = $this->sanitiseFieldTypes($field->fieldType);
                $field_type .= ((true === Str::contains($field->validations, 'required')) ? '!' : '');

                $schema[] = $field->name.': '.$field_type;
            }
        }
        $schema = array_merge($schema, $this->generateRelations());

        return ['$SCHEMA$' => implode(infy_nl_tab(1, 1), $schema)];
    }

    /**
     * Generate Relations.
     *
     * @return array
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
     * Get Relation Function Text.
     *
     * @param string      $relationship Relationship.
     * @param string|null $relationText Relation text.
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
     * @param string $functionName Function Name.
     * @param string $template     Template.
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
     * Sanitise Field Types.
     *
     * @param string $fieldType Field Type.
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
                $template = '$FUNCTION_NAME$: $RELATION_GRAPHQL_NAME$ @hasOne';

                break;
            case '1tm':
                $functionName = $pluralRelation;
                $template = '$FUNCTION_NAME$: [$RELATION_GRAPHQL_NAME$!]! @hasMany';

                break;
            case 'mt1':
                if (false === empty($relationship->relationName)) {
                    $singularRelation = $relationship->relationName;
                } elseif (true === isset($relationship->inputs[1])) {
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
