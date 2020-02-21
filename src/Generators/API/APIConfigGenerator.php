<?php

namespace PWWEB\Artomator\Generators\API;

use Illuminate\Support\Str;
use PWWEB\Artomator\Common\CommandData;

class APIConfigGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $configContents;

    /** @var string */
    private $configTemplate;

    /** @var string */
    private $search;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathApiConfig;
        $this->prepareConfigs();
        $this->configContents = file_get_contents($this->path);
        if (preg_match('/\/\/ Artomator Config Start(.*)\/\/ Artomator Config Stop/sU', $this->configContents) === 0) {
            $this->configContents .= "\n\n// Artomator Config Start\n// Artomator Config Stop";
        }

        $this->configContents = preg_replace('/(\/\/ Artomator Config Start)(.*)(\/\/ Artomator Config Stop)/sU', "$1\n" . $this->configs . "$3", $this->configContents);
    }

    public function prepareConfigs()
    {
        // As these should be loaded into the Config cache, we can use that to our advantage...

        $fileName = $this->path;

        // $existingConf = config('graphql');
        $queries = config('pwweb.graphql.queries');
        $mutations = config('pwweb.graphql.mutations');
        $types = config('pwweb.graphql.types');

        $newQuery = array($this->commandData->config->mPlural => 'App\\GraphQL\\Query\\' . $this->commandData->config->prefixes['ns'] . '\\' . $this->commandData->config->mHumanPlural . "Query::class");

        if (in_array($newQuery, $queries) === false)
        {
            $queries[] = $newQuery;
        }

        $newType = array($this->commandData->config->mName => 'App\\GraphQL\\Type\\' . $this->commandData->config->prefixes['ns'] . '\\' . $this->commandData->config->mHuman . "Type::class");

        if (in_array($newType, $types) === false)
        {
            $types[] = $newType;
        }

        var_dump($existingConf);

        die();

        if (file_exists($fileName)) {
            // Routes json exists:
            $fileConfig = file_get_contents($fileName);
            $fileConfig = json_decode($fileConfig, true);
        } else {
            $fileConfig = [];
        }

        if (empty($this->commandData->config->prefixes['route']))
        {
            // TODO: what to do when blank route prefix?
            $new = [
                'resources' => array($this->commandData->modelName => $this->commandData->modelName),
                'name' => strtolower($this->commandData->modelName),
            ];
            $configs = [ucfirst($this->commandData->modelName) => $new];
        } else {
            $prefixes = explode('.',$this->commandData->config->prefixes['route']);
            $configs = [];
            foreach (array_reverse($prefixes) as $key => $prefix) {
                $new = [
                    'prefix' => $prefix,
                    'name' => strtolower($prefix)
                ];
                if ($key === 0)
                {
                    $new['resources'] = array($this->commandData->modelName => $this->commandData->modelName);
                } else {
                    $new['group'] = $configs;
                }
                $configs = [ucfirst($prefix) => $new];
            }
        }
        // $fileConfig = array_replace_recursive($fileConfig, $configs);
        // file_put_contents($fileName, json_encode($fileConfig, JSON_PRETTY_PRINT));
        $this->commandData->commandComment("\API Config JSON File saved: ");
        $this->commandData->commandInfo($fileName);
        $this->configs = $fileConfig;
    }

    public function generate()
    {
        file_put_contents($this->path, $this->configContents);
        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' API config added.');
    }

    public function rollback()
    {
        if (Str::contains($this->configContents, $this->configTemplate)) {
            $this->configContents = str_replace($this->configTemplate, '', $this->configContents);
            file_put_contents($this->path, $this->configContents);
            $this->commandData->commandComment('scaffold routes deleted');
        }
    }
}
