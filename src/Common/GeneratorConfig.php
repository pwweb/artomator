<?php

namespace PWWEB\Artomator\Common;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData as Data;
use InfyOm\Generator\Common\GeneratorConfig as Config;

class GeneratorConfig extends Config
{
    /**
     * Path GraphQL.
     *
     * @var string
     */
    public $pathGraphQL;
    /**
     * Path Interface.
     *
     * @var string
     */
    public $pathInterface;

    /**
     * GraphQL Name.
     *
     * @var string
     */
    public $gName;
    /**
     * GraphQL Plural.
     *
     * @var string
     */
    public $gPlural;
    /**
     * GraphQL Camel.
     *
     * @var string
     */
    public $gCamel;
    /**
     * GraphQL Camel Plural.
     *
     * @var string
     */
    public $gCamelPlural;
    /**
     * GraphQL Snake.
     *
     * @var string
     */
    public $gSnake;
    /**
     * GraphQL Snake Plural.
     *
     * @var string
     */
    public $gSnakePlural;
    /**
     * GraphQL Dashed.
     *
     * @var string
     */
    public $gDashed;
    /**
     * GraphQL Dashed Plural.
     *
     * @var string
     */
    public $gDashedPlural;
    /**
     * GraphQL Slash.
     *
     * @var string
     */
    public $gSlash;
    /**
     * GraphQL Slash Plural.
     *
     * @var string
     */
    public $gSlashPlural;
    /**
     * GraphQL Human.
     *
     * @var string
     */
    public $gHuman;
    /**
     * GraphQL Human Plural.
     *
     * @var string
     */
    public $gHumanPlural;

    /**
     * GraphQL Namespace Interface.
     *
     * @var string
     */
    public $nsInterface;

    /**
     * Load Paths.
     *
     * @return void
     */
    public function loadPaths()
    {
        parent::loadPaths();

        $prefix = $this->prefixes['path'];

        if (false === empty($prefix)) {
            $prefix .= '/';
        }

        $this->pathFactory = $this->pathFactory.$prefix;

        $this->pathGraphQL = config('lighthouse.schema.register', base_path('graphql/schema.graphql'));

        $this->pathVues = config('pwweb.artomator.path.vues', resource_path('js/Pages/'));

        $this->pathLocales = config('infyom.laravel_generator.path.models_locale_files', base_path('resources/lang/en/models/')).$prefix;

        $this->pathSchemas = config('infyom.laravel_generator.path.schema_files', resource_path('model_schemas/')).$prefix;

        $this->pathInterface = config(
            'pwweb.artomator.path.interface',
            app_path('Interfaces/')
        ).$prefix;
    }

    /**
     * Load Dynamic Variables.
     *
     * @param Data $commandData Command Data.\
     *
     * @return Data
     */
    public function loadDynamicVariables(Data &$commandData)
    {
        parent::loadDynamicVariables($commandData);
        $commandData->addDynamicVariable('$LICENSE_PACKAGE$', config('pwweb.artomator.license.package'));
        $commandData->addDynamicVariable('$LICENSE_AUTHORS$', license_authors(config('pwweb.artomator.license.authors')));
        $commandData->addDynamicVariable('$LICENSE_COPYRIGHT$', config('pwweb.artomator.license.copyright'));
        $commandData->addDynamicVariable('$LICENSE$', config('pwweb.artomator.license.license'));
        $commandData->addDynamicVariable('$NAMESPACE_GRAPHQL_MODEL$', str_replace('\\', '\\\\', $this->nsModel));

        $prefix = $this->prefixes['ns'];

        if (false === empty($prefix)) {
            $prefix = '\\'.$prefix;
        }
        $this->nsInterface = config('pwweb.artomator.namespace.interface', 'App\Interfaces').$prefix;
        $commandData->addDynamicVariable('$NAMESPACE_INTERFACE$', $this->nsInterface);



        if (false === empty($this->prefixes['view'])) {
            $commandData->addDynamicVariable('$VUE_PREFIX$', $this->prefixes['view'].'/');
        } else {
            $commandData->addDynamicVariable('$VUE_PREFIX$', '');
        }

        return $commandData;
    }

    /**
     * Prepare GraphQL Names.
     *
     * @param string|null $name Name.
     *
     * @return void
     */
    public function prepareGraphQLNames($name = null)
    {
        if (true === is_null($name)) {
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

    /**
     * Load Dynamic GraphQL Variables.
     *
     * @param Data $commandData Command Data.
     *
     * @return Data
     */
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
