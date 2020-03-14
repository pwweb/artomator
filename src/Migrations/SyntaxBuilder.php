<?php

namespace PWWEB\Artomator\Migrations;

use Illuminate\Support\Pluralizer;
use Laracasts\Generators\GeneratorException;

class SyntaxBuilder
{
    /**
     * Create the schema for the arguments method.
     *
     * @param array $schema the schema to parse
     *
     * @throws GeneratorException
     *
     * @return string
     */
    public function createArgsSchema($schema)
    {
        $fields = $this->constructArgs($schema);

        return $fields;
    }

    /**
     * Construct the syntax for the resolves method.
     *
     * @param array $schema the schema to parse
     *
     * @throws GeneratorException
     *
     * @return string
     */
    public function createResolvesSchema($schema)
    {
        $fields = $this->constructResolves($schema);

        return $fields;
    }

    /**
     * Construct the syntax for the fields method.
     *
     * @param array $schema the schema to parse
     *
     * @throws GeneratorException
     *
     * @return string
     */
    public function createFieldsSchema($schema)
    {
        $fields = $this->constructFields($schema);

        return $fields;
    }

    /**
     * Construct the syntax for the data method.
     *
     * @param array $schema the schema to parse
     *
     * @throws GeneratorException
     *
     * @return string
     */
    public function createDataSchema($schema)
    {
        $data = $this->constructData($schema);

        return $data;
    }

    /**
     * Construct the schema arguments.
     *
     * @param array $schema the schema to parse
     *
     * @return string
     */
    private function constructArgs($schema)
    {
        if (true === empty($schema)) {
            return '';
        }

        $fields = array_map(
            function ($field) {
                return $this->addArg($field);
            },
            $schema
        );

        return implode("\n\t\t\t", $fields);
    }

    /**
     * Construct the syntax to add an argument.
     *
     * @param array $field the field to build the syntax
     *
     * @return string
     */
    private function addArg($field)
    {
        $syntax = sprintf("'%s' => ['name' => '%s', 'type' => Type::%s()]", $field['name'], $field['name'], $this->normaliseType($field['type']), $field['name']);

        return $syntax .= ',';
    }

    /**
     * Construct the schema resolvers.
     *
     * @param array $schema the schema to parse
     *
     * @return string
     */
    private function constructResolves($schema)
    {
        if (true === empty($schema)) {
            return '';
        }

        $fields = array_map(
            function ($field) {
                return $this->addResolve($field);
            },
            $schema
        );

        return implode("\n\t\t", $fields);
    }

    /**
     * Construct the syntax to add a resolve.
     *
     * @param array $field the field to build the syntax
     *
     * @return string
     */
    private function addResolve($field)
    {
        $syntax = sprintf("if(isset(\$args['%s']) === true) {\n\t\t\treturn DummyModelClass::where('%s', \$args['%s'])->get();\n\t\t}", $field['name'], $field['name'], $field['name']);

        return $syntax .= "\n";
    }

    /**
     * Construct the schema resolvers.
     *
     * @param array $schema the schema to parse
     *
     * @return string
     */
    private function constructFields($schema)
    {
        if (true === empty($schema)) {
            return '';
        }

        $fields = array_map(
            function ($field) {
                return $this->addField($field);
            },
            $schema
        );

        return implode("\n\t\t\t", $fields);
    }

    /**
     * Construct the syntax to add a resolve.
     *
     * @param array $field the field to build the syntax
     *
     * @return string
     */
    private function addField($field)
    {
        if (false !== ($name = strstr($field['name'], '_id', true))) {
            // Then we have a foreign key?
            $field['name'] = substr(strrchr($name, '_'), 1);
            $format = "'%1\$s' => [\n\t\t\t\t'type' => Type::listOf(GraphQL::type('%3\$s')),\n\t\t\t\t'description' => 'The %1\$s of the model',\n\t\t\t]";
        } else {
            $format = "'%1\$s' => [\n\t\t\t\t'type' => Type::%2\$s(),\n\t\t\t\t'description' => 'The %1\$s of the model',\n\t\t\t]";
        }
        $syntax = sprintf($format, $field['name'], $this->normaliseType($field['type']), Pluralizer::singular($field['name']));

        return $syntax .= ',';
    }

    /**
     * Construct the schema data.
     *
     * @param array $schema the schema to parse
     *
     * @return string
     */
    private function constructData($schema)
    {
        if (true === empty($schema)) {
            return '';
        }

        $fields = array_map(
            function ($field) {
                return $this->addData($field);
            },
            $schema
        );

        return implode("\n\t\t\t", $fields);
    }

    /**
     * Construct the syntax to add data.
     *
     * @param array $field the field to build the syntax
     *
     * @return string
     */
    private function addData($field)
    {
        $syntax = sprintf('$DummyModelVariable->%1$s = $request->%1$s', $field['name']);

        return $syntax .= ';';
    }

    /**
     * Normalise the types.
     *
     * @param string $type type name to normalise
     *
     * @return string
     */
    private function normaliseType($type)
    {
        switch ($type) {
            case 'text':
            case 'varchar':
            case 'date':
                $type = 'string';
                break;
            case 'integer':
            case 'int':
            case 'int unsigned':
                $type = 'int';
                break;
            case 'tinyint':
                $type = 'boolean';
                // no break
            default:
                break;
        }

        return strtolower($type);
    }
}
