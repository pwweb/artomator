<?php

namespace PWWEB\Artomator\Generators\Scaffold;

use Illuminate\Support\Str;
use PWWEB\Artomator\Common\CommandData;

class RoutesGenerator
{
    /**
     * Command data instance.
     *
     * @var CommandData
     */
    private $commandData;

    /**
     * Path variable.
     *
     * @var string
     */
    private $path;

    /**
     * Route Contents.
     *
     * @var string
     */
    private $routeContents;

    /**
     * Route template.
     *
     * @var string
     */
    private $routesTemplate;

    /**
     * Routes array.
     *
     * @var string
     */
    private $routes;

    /**
     * Constructor.
     *
     * @param CommandData $commandData Command data passed in from above.
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRoutes;
    }

    /**
     * Prepare the routes array.
     *
     * @return void
     */
    public function prepareRoutes()
    {
        $fileName = $this->path.'.json';

        if (file_exists($fileName) === true) {
            // Routes json exists:
            $fileRoutes = file_get_contents($fileName);
            $fileRoutes = json_decode($fileRoutes, true);
        } else {
            $fileRoutes = [];
        }

        if (empty($this->commandData->config->prefixes['route']) === true) {
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
        }
        $fileRoutes = array_replace_recursive($fileRoutes, $routes);
        file_put_contents($fileName, json_encode($fileRoutes, JSON_PRETTY_PRINT));
        $this->commandData->commandComment("\nRoute JSON File saved: ");
        $this->commandData->commandInfo($fileName);
        $this->routes = $this->buildText($fileRoutes);
    }

    /**
     * Generator function.
     *
     * @return void
     */
    public function generate()
    {
        $this->prepareRoutes();
        $this->routeContents = file_get_contents($this->path);
        if (1 !== preg_match('/\/\/ Artomator Routes Start(.*)\/\/ Artomator Routes Stop/sU', $this->routeContents)) {
            $this->routeContents .= "\n\n// Artomator Routes Start\n// Artomator Routes Stop";
        }

        $this->routeContents = preg_replace('/(\/\/ Artomator Routes Start)(.*)(\/\/ Artomator Routes Stop)/sU', "$1\n".$this->routes.'$3', $this->routeContents);

        file_put_contents($this->path, $this->routeContents);
        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' routes added.');
    }

    /**
     * Re-generator the routes function.
     *
     * @return void
     */
    public function regenerate()
    {
        $fileName = $this->path.'.json';

        if (file_exists($fileName) === true) {
            // Routes json exists:
            $fileRoutes = file_get_contents($fileName);
            $fileRoutes = json_decode($fileRoutes, true);
        } else {
            $fileRoutes = [];
        }
        $this->routes = $this->buildText($fileRoutes);
        $this->routeContents = file_get_contents($this->path);
        if (1 !== preg_match('/\/\/ Artomator Routes Start(.*)\/\/ Artomator Routes Stop/sU', $this->routeContents)) {
            $this->routeContents .= "\n\n// Artomator Routes Start\n// Artomator Routes Stop";
        }

        $this->routeContents = preg_replace('/(\/\/ Artomator Routes Start)(.*)(\/\/ Artomator Routes Stop)/sU', "$1\n".$this->routes.'$3', $this->routeContents);

        file_put_contents($this->path, $this->routeContents);
        $this->commandData->commandComment("\nRoutes regenerated.");
    }

    /**
     * Rollback function.
     *
     * @return void
     */
    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate) === true) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->commandData->commandComment('scaffold routes deleted');
        }
    }

    /**
     * Template text builder function.
     *
     * @param array $routes Routes array to process
     * @param int   $indent Indent counter
     *
     * @return void
     */
    private function buildText(array $routes, int $indent = 0)
    {
        $templateContent = '';
        foreach ($routes as $route_key => $route) {
            $templateString = '';
            $tabs = (isset($route['prefix']) === true) ? (($indent * 3) + 3) : 0;
            if (isset($route['custom']) === true) {
                foreach ($route['custom'] as $custom_key => $custom) {
                    if (true === isset($custom['function']) && $custom['function'] !== '') {
                        $custom['function'] = '@'.$custom['function'];
                    }
                    $vars = [
                        '$ITERATION_CUSTOM_METHOD$' => $custom['method'],
                        '$ITERATION_CUSTOM_ENDPOINT$' => $custom['endpoint'],
                        '$ITERATION_CUSTOM_CONTROLLER$' => $custom['controller'],
                        '$ITERATION_CUSTOM_FUNCTION$' => $custom['function'],
                        '$ITERATION_CUSTOM_NAME$' => $custom['name'],
                        '$INDENT$' => infy_tabs($tabs),
                    ];
                    $templateString .= get_artomator_template('scaffold.routes.prefixed.custom');
                    $templateString = fill_template($vars, $templateString);
                }
            }
            if (isset($route['resources'])) {
                $tabs = (isset($route['prefix'])) ? (($indent * 3) + 3) : 0;
                foreach (array_keys($route['resources']) as $resource_key) {
                    $vars = [
                        '$ITERATION_MODEL_NAME_PLURAL_CAMEL$' => Str::camel(Str::plural($resource_key)),
                        '$ITERATION_MODEL_NAME$'              => $resource_key,
                        '$INDENT$'                            => infy_tabs($tabs),
                    ];
                    $templateString .= get_artomator_template('scaffold.routes.prefixed.route');
                    $templateString = fill_template($vars, $templateString);
                }
            }
            if ((isset($route['group'])) === true) {
                $templateString .= $this->buildText($route['group'], ($indent + 1));
            }
            if ((isset($route['prefix'])) === true) {
                $vars = [
                    '$ITERATION_NAMESPACE_CAMEL$' => ucfirst($route_key),
                    '$ITERATION_NAMESPACE_LOWER$' => strtolower($route_key),
                    '$INDENT$'                    => infy_tabs($indent * 3),
                ];
                $templateString = get_artomator_template('scaffold.routes.prefixed.namespace').$templateString.get_artomator_template('scaffold.routes.prefixed.closure');
                $templateString = fill_template($vars, $templateString);
            }
            $templateContent .= $templateString;
        }

        return $templateContent;
    }
}
