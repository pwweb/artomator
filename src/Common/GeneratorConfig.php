<?php

namespace PWWEB\Artomator\Common;

use InfyOm\Generator\Common\CommandData as Data;
use InfyOm\Generator\Common\GeneratorConfig as Config;

class GeneratorConfig extends Config
{
    /* Path variables */
    public $pathGraphQL;

    public function init(Data &$commandData, $options = null)
    {
        parent::$availableOptions[] = 'gqlName';
        parent::init($commandData, $options);
    }

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
}
