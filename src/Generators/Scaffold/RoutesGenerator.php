<?php

namespace PWWEB\Artomator\Generators\Scaffold;

use Illuminate\Support\Str;
use PWWEB\Artomator\Common\CommandData;

class RoutesGenerator
{
    /**
     * @var CommandData
     */
    private $commandData;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $routeContents;

    /**
     * @var string
     */
    private $routesTemplate;

    /**
     * @var string
     */
    private $search;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRoutes;
        $this->prepareRoutes();
        $this->routeContents = file_get_contents($this->path);
        if (0 === preg_match('/\/\/ Artomator Routes Start(.*)\/\/ Artomator Routes Stop/sU', $this->routeContents)) {
            $this->routeContents .= "\n\n// Artomator Routes Start\n// Artomator Routes Stop";
        }

        $this->routeContents = preg_replace('/(\/\/ Artomator Routes Start)(.*)(\/\/ Artomator Routes Stop)/sU', "$1\n" . $this->routes . '$3', $this->routeContents);
    }

    public function prepareRoutes()
    {
        $fileName = $this->path . '.json';

        if (file_exists($fileName)) {
            // Routes json exists:
            $fileRoutes = file_get_contents($fileName);
            $fileRoutes = json_decode($fileRoutes, true);
        } else {
            $fileRoutes = [];
        }

        if (empty($this->commandData->config->prefixes['route'])) {
            // TODO: what to do when blank route prefix?
            $new = [
                'resources' => [$this->commandData->modelName => $this->commandData->modelName],
                'name'      => strtolower($this->commandData->modelName),
            ];
            $routes = [ucfirst($this->commandData->modelName) => $new];
        } else {
            $prefixes = explode('.', $this->commandData->config->prefixes['route']);
            $routes = [];
            foreach (array_reverse($prefixes) as $key => $prefix) {
                $new = [
                    'prefix' => $prefix,
                    'name'   => strtolower($prefix),
                ];
                if (0 === $key) {
                    $new['resources'] = [$this->commandData->modelName => $this->commandData->modelName];
                } else {
                    $new['group'] = $routes;
                }
                $routes = [ucfirst($prefix) => $new];
            }
        }//end if
        $fileRoutes = array_replace_recursive($fileRoutes, $routes);
        file_put_contents($fileName, json_encode($fileRoutes, JSON_PRETTY_PRINT));
        $this->commandData->commandComment("\nRoute JSON File saved: ");
        $this->commandData->commandInfo($fileName);
        $this->routes = $this->buildText($fileRoutes);
    }

    public function generate()
    {
        file_put_contents($this->path, $this->routeContents);
        $this->commandData->commandComment("\n" . $this->commandData->config->mCamelPlural . ' routes added.');
    }

    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->commandData->commandComment('scaffold routes deleted');
        }
    }

    private function buildText($routes, $indent = 0)
    {
        $templateString = '';
        foreach ($routes as $key => $route) {
            if ((isset($route['group']) && is_array($route['group'])) || 0 != $indent) {
                $vars = [
                    '$ITERATION_NAMESPACE_CAMEL$' => ucfirst($key),
                    '$ITERATION_NAMESPACE_LOWER$' => strtolower($key),
                    '$INDENT$'                    => infy_tabs($indent * 3),
                ];
                $templateString .= get_template('scaffold.routes.prefixed.namespace', 'artomator');
                $templateString = fill_template($vars, $templateString);
            }
            if (isset($route['resources'])) {
                $tabs = ($indent > 0) ? (($indent * 3) + 3) : 0;
                foreach ($route['resources'] as $key => $resource) {
                    $vars = [
                        '$ITERATION_MODEL_NAME_PLURAL_CAMEL$' => Str::camel(Str::plural($key)),
                        '$ITERATION_MODEL_NAME$'              => $key,
                        '$INDENT$'                            => infy_tabs($tabs),
                    ];
                    $templateString .= get_template('scaffold.routes.prefixed.route', 'artomator');
                    $templateString = fill_template($vars, $templateString);
                }
                if (0 == $indent) {
                    continue;
                }
            }
            if ((isset($route['group']) && is_array($route['group']))) {
                $templateString .= $this->buildText($route['group'], ($indent + 1));
            }
            $vars = [
                '$INDENT$' => infy_tabs(($indent * 3)),
            ];
            $templateString .= get_template('scaffold.routes.prefixed.closure', 'artomator');
            $templateString = fill_template($vars, $templateString);
        }//end foreach

        return $templateString;
    }
}
