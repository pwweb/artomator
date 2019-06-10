<?php

namespace PWWEB\Artomator\Migrations;

use Laracasts\Generators\GeneratorException;

class SyntaxBuilder
{
    /**
     * A template to be inserted.
     *
     * @var string
     */
    private $template;

    /**
     * Create the PHP syntax for the given schema.
     *
     * @param  array $schema
     * @return string
     * @throws GeneratorException
     */
    public function create($schema)
    {
        return [
            '{{schema_args}}' => $this->createArgsSchema($schema),
            '{{schema_resolves}}' => $this->createResolvesSchema($schema),
        ];
    }

    /**
     * Create the schema for the "up" method.
     *
     * @param  string $schema
     * @return string
     * @throws GeneratorException
     */
    private function createArgsSchema($schema)
    {
        $fields = $this->constructArgs($schema);

        return $fields;
    }

    /**
     * Construct the syntax for a down field.
     *
     * @param  array $schema
     * @return string
     * @throws GeneratorException
     */
    private function createResolvesSchema($schema)
    {
        $fields = $this->constructResolves($schema);

        return $fields;
    }

    /**
     * Construct the schema arguments.
     *
     * @param  array $schema
     * @return array
     */
    private function constructArgs($schema)
    {
        if (!$schema) {
            return '';
        }

        $fields = array_map(function ($field) {
            return $this->addArg($field);
        }, $schema);

        return implode("\n\t\t\t", $fields);
    }


    /**
     * Construct the syntax to add an argument.
     *
     * @param  string $field
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
     * @param  array $schema
     * @return array
     */
    private function constructResolves($schema)
    {
        if (!$schema) {
            return '';
        }

        $fields = array_map(function ($field) {
            return $this->addResolve($field);
        }, $schema);

        return implode("\n\t\t", $fields);
    }


    /**
     * Construct the syntax to add a resolve.
     *
     * @param  string $field
     * @return string
     */
    private function addResolve($field)
    {
        $syntax = sprintf("if(isset(\$args['%s']) === true) {\n\t\t\treturn DummyModelClass::where('%s', \$args['%s'])->get();\n\t\t}", $field['name'], $field['name'], $field['name']);

        return $syntax .= "\n";
    }

    /**
     * Normalise the types.
     *
     * @param string $type
     *
     * @return string
     */
    private function normaliseType($type)
    {
        switch ($type) {
            case 'text':
            case 'date':
                $type = 'string';
                break;
            case 'integer':
                $type = 'int';
                break;
            default:
                break;
        }

        return strtolower($type);
    }
}
