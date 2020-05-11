<?php

namespace PWWEB\Artomator\Common;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData as Data;
use InfyOm\Generator\Common\GeneratorConfig as Config;

class GeneratorConfig extends Config
{
    /* Path variables */
    public $pathGraphQL;

    /* Model Names */
    public $gName;
    public $gPlural;
    public $gCamel;
    public $gCamelPlural;
    public $gSnake;
    public $gSnakePlural;
    public $gDashed;
    public $gDashedPlural;
    public $gSlash;
    public $gSlashPlural;
    public $gHuman;
    public $gHumanPlural;

    public function loadPaths()
    {
        parent::loadPaths();

        $this->pathGraphQL = config('lighthouse.schema.register', base_path('graphql/schema.graphql'));
    }

    public function loadDynamicVariables(Data &$commandData)
    {
        parent::loadDynamicVariables($commandData);
        $commandData->addDynamicVariable('$LICENSE_PACKAGE$', config('pwweb.artomator.license.package'));
        $commandData->addDynamicVariable('$LICENSE_AUTHORS$', license_authors(config('pwweb.artomator.license.authors')));
        $commandData->addDynamicVariable('$LICENSE_COPYRIGHT$', config('pwweb.artomator.license.copyright'));
        $commandData->addDynamicVariable('$LICENSE$', config('pwweb.artomator.license.license'));
        $commandData->addDynamicVariable('$NAMESPACE_GRAPHQL_MODEL$', str_replace('\\', '\\\\', $this->nsModel));

        return $commandData;
    }

    public function prepareGraphQLNames($name = null)
    {
        if (is_null($name)) {
            $name = $this->mName;
        }
        $this->gName = $name;
        $this->gPlural = Str::plural($this->gName);
        $this->gCamel = Str::camel($this->gName);
        $this->gCamelPlural = Str::camel($this->gPlural);
        $this->gSnake = Str::snake($this->gName);
        $this->gSnakePlural = Str::snake($this->gPlural);
        $this->gDashed = str_replace('_', '-', Str::snake($this->gSnake));
        $this->gDashedPlural = str_replace('_', '-', Str::snake($this->gSnakePlural));
        $this->gSlash = str_replace('_', '/', Str::snake($this->gSnake));
        $this->gSlashPlural = str_replace('_', '/', Str::snake($this->gSnakePlural));
        $this->gHuman = Str::title(str_replace('_', ' ', Str::snake($this->gSnake)));
        $this->gHumanPlural = Str::title(str_replace('_', ' ', Str::snake($this->gSnakePlural)));
    }

    public function loadDynamicGraphQLVariables(Data &$commandData)
    {
        $commandData->addDynamicVariable('$GRAPHQL_NAME$', $this->gName);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_CAMEL$', $this->gCamel);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_PLURAL$', $this->gPlural);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_PLURAL_CAMEL$', $this->gCamelPlural);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_SNAKE$', $this->gSnake);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_PLURAL_SNAKE$', $this->gSnakePlural);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_DASHED$', $this->gDashed);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_PLURAL_DASHED$', $this->gDashedPlural);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_SLASH$', $this->gSlash);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_PLURAL_SLASH$', $this->gSlashPlural);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_HUMAN$', $this->gHuman);
        $commandData->addDynamicVariable('$GRAPHQL_NAME_PLURAL_HUMAN$', $this->gHumanPlural);

        return $commandData;
    }
}
