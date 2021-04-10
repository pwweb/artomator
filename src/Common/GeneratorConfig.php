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
     * Path Contract.
     *
     * @var string
     */
    public $pathContract;

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
     * GraphQL Namespace Contract.
     *
     * @var string
     */
    public $nsContract;

    /**
     * Command Options.
     *
     * @var array
     */
    public static $availableOptions = [
        'fieldsFile',
        'jsonFromGUI',
        'tableName',
        'fromTable',
        'ignoreFields',
        'save',
        'primary',
        'prefix',
        'paginate',
        'skip',
        'datatables',
        'views',
        'relations',
        'plural',
        'softDelete',
        'forceMigrate',
        'factory',
        'seeder',
        'repositoryPattern',
        'resources',
        'localized',
        'connection',
        'jqueryDT',
        'vue',
    ];

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

        $viewPrefix = $this->prefixes['view'];

        if (false === empty($viewPrefix)) {
            $viewPrefix .= '/';
        }

        $this->pathFactory = $this->pathFactory.$prefix;

        $this->pathGraphQL = config('lighthouse.schema.register', base_path('graphql/schema.graphql'));

        $this->pathVues = config('pwweb.artomator.path.vues', resource_path('js/Pages/')).$viewPrefix.$this->mSnakePlural.'/';

        $this->pathLocales = config('infyom.laravel_generator.path.models_locale_files', base_path('resources/lang/en/models/')).$prefix;

        $this->pathSchemas = config('infyom.laravel_generator.path.schema_files', resource_path('model_schemas/')).$prefix;

        $this->pathContract = config(
            'pwweb.artomator.path.contract',
            app_path('Contracts/')
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
        $this->nsContract = config('pwweb.artomator.namespace.contract', 'App\Contracts').$prefix;
        $commandData->addDynamicVariable('$NAMESPACE_CONTRACT$', $this->nsContract);

        if (false === empty($this->prefixes['view'])) {
            $commandData->addDynamicVariable('$VUE_PREFIX$', $this->prefixes['view'].'/');
        } else {
            $commandData->addDynamicVariable('$VUE_PREFIX$', '');
        }

        if (false === empty($this->prefixes['path'])) {
            $commandData->addDynamicVariable('$LANG_PREFIX$', Str::lower($this->prefixes['path'].'/'));
        } else {
            $commandData->addDynamicVariable('$LANG_PREFIX$', '');
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

    /**
     * Prepare Options.
     *
     * @param Data $commandData Command Data.
     *
     * @return void
     */
    public function prepareOptions(Data &$commandData)
    {
        foreach (self::$availableOptions as $option) {
            $this->options[$option] = $commandData->commandObj->option($option);
        }

        if (true === isset($options['fromTable']) && true === $this->options['fromTable']) {
            if (false === $this->options['tableName']) {
                $commandData->commandError('tableName required with fromTable option.');
                exit;
            }
        }

        if (true === empty($this->options['save'])) {
            $this->options['save'] = config('infyom.laravel_generator.options.save_schema_file', true);
        }

        if (true === empty($this->options['vue'])) {
            $this->options['vue'] = config('pwweb.artomator.options.vue_files', false);
        }

        if (true === empty($this->options['localized'])) {
            $this->options['localized'] = config('infyom.laravel_generator.options.localized', false);
        }

        if (true === $this->options['localized']) {
            $commandData->getTemplatesManager()->setUseLocale(true);
        }

        $this->options['softDelete'] = config('infyom.laravel_generator.options.softDelete', false);
        $this->options['repositoryPattern'] = config('infyom.laravel_generator.options.repository_pattern', true);
        $this->options['resources'] = config('infyom.laravel_generator.options.resources', true);
        if (false === empty($this->options['skip'])) {
            $this->options['skip'] = array_map('trim', explode(',', $this->options['skip']));
        }

        if (false === empty($this->options['datatables'])) {
            if ('true' === strtolower($this->options['datatables'])) {
                $this->addOns['datatables'] = true;
            } else {
                $this->addOns['datatables'] = false;
            }
        }
    }
}
