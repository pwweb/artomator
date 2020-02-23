<?php

namespace PWWEB\Artomator\Common;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Common\GeneratorConfig as Config;

class GeneratorConfig extends Config
{
    /* Path variables */
    public $pathGraphQL;

    public function loadNamespaces(CommandData &$commandData)
    {
        parent::loadNamespaces($commandData);
        $prefix = $this->prefixes['ns'];
        $this->nsGraphQLQuery = config(
            'pwweb.artomator.namespace.graphql_query',
            'App\Http\GraphQL\Queries'
        ).$prefix;
        $this->nsGraphQLMutation = config('pwweb.artomator.namespace.graphql_mutation', 'App\Http\GraphQL\Mutations').$prefix;
        $this->nsGraphQLType = config('pwweb.artomator.namespace.graphql_type', 'App\Http\GraphQL\Types').$prefix;
    }

    public function loadPaths()
    {
        parent::loadPaths();
        $prefix = $this->prefixes['path'];

        $this->pathGraphQLQuery = config(
            'pwweb.artomator.path.graphql_query',
            app_path('Http/GraphQL/Queries/')
        ).$prefix;

        $this->pathGraphQLMutation = config(
            'pwweb.artomator.path.graphql_mutation',
            app_path('Http/GraphQL/Mutations/')
        ).$prefix;

        $this->pathGraphQLRoutes = config('pwweb.artomator.path.graphql_routes', base_path('routes/graphql.php'));

        $this->pathGraphQLType = config('pwweb.artomator.path.graphql_type', app_path('Http/GraphQL/Types/')).$prefix;

        $this->pathGraphQLConfig = config('pwweb.artomator.path.graphql_config', base_path('config/graphiql.php'));
    }

    public function loadDynamicVariables(CommandData &$commandData)
    {
        parent::loadDynamicVariables($commandData);
        $commandData->addDynamicVariable('$NAMESPACE_API_QUERY$', $this->nsGraphQLQuery);
        $commandData->addDynamicVariable('$NAMESPACE_API_MUTATION$', $this->nsGraphQLMutation);
        $commandData->addDynamicVariable('$LICENSE_PACKAGE$', config('pwweb.artomator.license.package'));
        $commandData->addDynamicVariable('$LICENSE_AUTHORS$', license_authors(config('pwweb.artomator.license.authors')));
        $commandData->addDynamicVariable('$LICENSE_COPYRIGHT$', config('pwweb.artomator.license.copyright'));
        $commandData->addDynamicVariable('$LICENSE$', config('pwweb.artomator.license.license'));

        return $commandData;
    }
}
