<?php

namespace PWWEB\Artomator\Generators;

use File;
use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;

/**
 * Class ViewServiceProviderGenerator.
 */
class ViewServiceProviderGenerator extends BaseGenerator
{
    private $commandData;

    /**
     * ViewServiceProvider constructor.
     *
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
    }

    /**
     * Generate ViewServiceProvider.
     */
    public function generate()
    {
        $templateData = get_artomator_template('view_service_provider');

        $destination = $this->commandData->config->pathViewProvider;

        $fileName = basename($this->commandData->config->pathViewProvider);

        if (File::exists($destination)) {
            return;
        }
        file_put_contents($destination, $templateData);

        $this->commandData->commandComment($fileName.' published');
        $this->commandData->commandInfo($fileName);
    }

    /**
     * @param string      $views
     * @param string      $variableName
     * @param string      $columns
     * @param string      $tableName
     * @param string|null $modelName
     */
    public function addViewVariables($views, $variableName, $columns, $tableName, $modelName = null)
    {
        if (! empty($modelName)) {
            $model = $modelName;
        } else {
            $model = model_name_from_table_name($tableName);
        }

        $this->commandData->addDynamicVariable('$COMPOSER_VIEWS$', $views);
        $this->commandData->addDynamicVariable('$COMPOSER_VIEW_MODEL$', $model);
        $this->commandData->addDynamicVariable('$COMPOSER_VIEW_COLUMNS$', $columns);

        $mainViewContent = $this->addViewComposer();
        $mainViewContent = $this->addNamespace($model, $mainViewContent);
        $mainViewContent = $this->addSelect($model, $views, $mainViewContent);
        $this->addCustomProvider();

        file_put_contents($this->commandData->config->pathViewProvider, $mainViewContent);
        $this->commandData->commandComment('View service provider file updated.');
    }

    public function addViewComposer()
    {
        $mainViewContent = file_get_contents($this->commandData->config->pathViewProvider);
        $newViewStatement = get_artomator_template('scaffold.view_composer');
        $newViewStatement = fill_template($this->commandData->dynamicVars, $newViewStatement);

        $newViewStatement = infy_nl(1).$newViewStatement;
        preg_match_all('/}(\s)}/', $mainViewContent, $matches);

        $totalMatches = count($matches[0]);
        $lastSeederStatement = $matches[0][$totalMatches - 1];

        $replacePosition = strpos($mainViewContent, $lastSeederStatement);
        $mainViewContent = substr_replace(
            $mainViewContent,
            $newViewStatement,
            $replacePosition,
            0
        );

        return $mainViewContent;
    }

    public function addCustomProvider()
    {
        $configFile = base_path().'/config/app.php';
        $file = file_get_contents($configFile);
        $searchFor = 'Illuminate\View\ViewServiceProvider::class,';
        $customProviders = strpos($file, $searchFor);

        $isExist = strpos($file, "App\Providers\ViewServiceProvider::class");
        if ($customProviders && ! $isExist) {
            $newChanges = substr_replace(
                $file,
                infy_nl().infy_tab(8).'\App\Providers\ViewServiceProvider::class,',
                $customProviders + strlen($searchFor),
                0
            );
            file_put_contents($configFile, $newChanges);
        }
    }

    public function addNamespace($model, $mainViewContent)
    {
        $newModelStatement = 'use '.$this->commandData->config->nsInterface.'\\'.$model.'RepositoryInterface as '.$model.';';
        $isNameSpaceExist = strpos($mainViewContent, $newModelStatement);
        $newModelStatement = infy_nl().$newModelStatement;
        if (! $isNameSpaceExist) {
            preg_match_all('/namespace(.*)/', $mainViewContent, $matches);
            $totalMatches = count($matches[0]);
            $nameSpaceStatement = $matches[0][$totalMatches - 1];
            $replacePosition = strpos($mainViewContent, $nameSpaceStatement);
            $mainViewContent = substr_replace(
                $mainViewContent,
                $newModelStatement,
                $replacePosition + strlen($nameSpaceStatement),
                0
            );
            $mainViewContent = $this->addProperty($model, $mainViewContent);
            $mainViewContent = $this->addBoot($model, $mainViewContent);
        }

        return $mainViewContent;
    }

    public function addProperty($model, $mainViewContent)
    {
        $newPropertyStatement = "\n\n\t/**\n\t * The "
            .ucfirst($model)." repository.\n\t *\n\t * @var "
            .ucfirst($model)."\n\t */\n\tprivate \$"
            .lcfirst($model)."Repository;\n\n";

        preg_match_all('/\{/', $mainViewContent, $matches);
        $totalMatches = count($matches[0]);
        $propertyStatement = $matches[0][0];
        $replacePosition = strpos($mainViewContent, $propertyStatement);
        $mainViewContent = substr_replace(
            $mainViewContent,
            $newPropertyStatement,
            $replacePosition + strlen($propertyStatement),
            0
        );

        return $mainViewContent;
    }

    public function addBoot($model, $mainViewContent)
    {
        $newBootStatement = "\n\t\t"
            .ucfirst($model).' $'
            .lcfirst($model).'Repo,';

        preg_match_all('/boot\(.*?\)/mis', $mainViewContent, $matches);
        $totalMatches = count($matches[0]);
        $bootStatement = $matches[0][$totalMatches - 1];
        $replacePosition = strpos($mainViewContent, $bootStatement);
        $mainViewContent = substr_replace(
            $mainViewContent,
            $newBootStatement,
            $replacePosition + strlen($bootStatement) - 1,
            0
        );

        $newBootStatement = "\n\t\t\$this->"
            .lcfirst($model).'Repository = $'
            .lcfirst($model).'Repo;';

        preg_match_all('/Repo\;$/', $mainViewContent, $matches);
        $totalMatches = count($matches[0]);
        if ($totalMatches <= 0) {
            preg_match_all('/boot\(.*?\{/s', $mainViewContent, $matches);
            $totalMatches = count($matches[0]);
        }
        $bootStatement = $matches[0][$totalMatches - 1];
        $replacePosition = strpos($mainViewContent, $bootStatement);
        $mainViewContent = substr_replace(
            $mainViewContent,
            $newBootStatement,
            $replacePosition + strlen($bootStatement),
            0
        );

        $newBootStatement = "\t * @param "
            .ucfirst($model).' $'
            .lcfirst($model).'Repo '
            .ucfirst($model).' repo';


        preg_match_all('/Bootstrap(.*)services\./mis', $mainViewContent, $matches);
        $totalMatches = count($matches[0]);
        echo $totalMatches."\n";
        $newBootStatement = "\n\n".$newBootStatement;
        $bootStatement = $matches[0][$totalMatches - 1];
        $replacePosition = strpos($mainViewContent, $bootStatement);
        $mainViewContent = substr_replace(
            $mainViewContent,
            $newBootStatement,
            $replacePosition + strlen($bootStatement),
            0
        );

        return $mainViewContent;
    }

    public function addSelect($model, $view, $mainViewContent)
    {
        $newModelStatement = '\''.$model.'\' => [';
        $length = strlen($newModelStatement);
        $isNameSpaceExist = strpos($mainViewContent, $newModelStatement);
        $newModelStatement = infy_nl().$newModelStatement.infy_nl_tab().'\''.$view.'\','.infy_nl();
        if (! $isNameSpaceExist) {
            preg_match_all('/selects = \[/', $mainViewContent, $matches);
            $totalMatches = count($matches[0]);
            $nameSpaceStatement = $matches[0][$totalMatches - 1];
            $replacePosition = strpos($mainViewContent, $nameSpaceStatement);
            $mainViewContent = substr_replace(
                $mainViewContent,
                $newModelStatement.'],'.infy_nl(),
                $replacePosition + strlen($nameSpaceStatement),
                0
            );
        } else {
            $mainViewContent = substr_replace(
                $mainViewContent,
                $newModelStatement,
                $isNameSpaceExist,
                $length
            );
        }

        return $mainViewContent;
    }
}
