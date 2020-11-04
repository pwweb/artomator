<?php

namespace PWWEB\Artomator\Generators\Scaffold;

use Illuminate\Support\Arr;
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
     * Clasess array.
     *
     * @var string
     */
    private $classes;

    /**
     * Classes array.
     *
     * @var string[]
     */
    private $classNames;

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

        if (true === file_exists($fileName)) {
            // Routes json exists:
            $fileRoutes = file_get_contents($fileName);
            $fileRoutes = json_decode($fileRoutes, true);
        } else {
            $fileRoutes = [];
        }
        $nsControllerName = $this->commandData->config->nsController.'\\'.$this->commandData->modelName.'Controller';
        if (true === empty($this->commandData->config->prefixes['route'])) {
            $new = [
                'resources' => [
                    $this->commandData->modelName => [
                        'only' => '*',
                        'controller' => $nsControllerName,
                        'as' => $this->commandData->modelName.'Controller',
                    ],
                ],
                'name' => strtolower($this->commandData->modelName),
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
                    $new['resources'] = [
                        $this->commandData->modelName => [
                            'only' => '*',
                            'controller' => $nsControllerName,
                            'as' => $this->commandData->modelName.'Controller',
                        ],
                    ];
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
        $this->classes = $this->buildClasses();
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

        $this->routeContents = preg_replace(
            '/(\/\/ Artomator Routes Start)(.*)(\/\/ Artomator Routes Stop)/sU',
            "$1\n".$this->routes.'$3',
            $this->routeContents
        );

        if (1 !== preg_match(
            '/\/\/ Artomator Class References Start(.*)\/\/ Artomator Class References Stop/sU',
            $this->routeContents
        )) {
            $this->routeContents = preg_replace(
                '/(<\?php)/sU',
                "<?php\n\n// Artomator Class References Start\n// Artomator Class References Stop",
                $this->routeContents
            );
        }

        $this->routeContents = preg_replace(
            '/(\/\/ Artomator Class References Start)(.*)(\/\/ Artomator Class References Stop)/sU',
            "$1\n".$this->classes.'$3',
            $this->routeContents
        );

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

        if (true === file_exists($fileName)) {
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

        $this->routeContents = preg_replace(
            '/(\/\/ Artomator Routes Start)(.*)(\/\/ Artomator Routes Stop)/sU',
            "$1\n".$this->routes.'$3',
            $this->routeContents
        );

        if (1 !== preg_match(
            '/\/\/ Artomator Class References Start(.*)\/\/ Artomator Class References Stop/sU',
            $this->routeContents
        )) {
            $this->routeContents = preg_replace(
                '/(<\?php)/sU',
                "<?php\n\n// Artomator Class References Start\n// Artomator Class References Stop",
                $this->routeContents
            );
        }

        $this->routeContents = preg_replace(
            '/(\/\/ Artomator Class References Start)(.*)(\/\/ Artomator Class References Stop)/sU',
            "$1\n".$this->classes.'$3',
            $this->routeContents
        );

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
        if (true === Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->commandData->commandComment('scaffold routes deleted');
        }
    }

    /**
     * Template text builder function.
     *
     * @param array  $routes Routes array to process
     * @param int    $indent Indent counter
     * @param string $parent Parent prefix for fallback route
     *
     * @return void
     */
    private function buildText(array $routes, int $indent = 0, string $parent = '')
    {
        $templateContent = '';
        foreach ($routes as $route_key => $route) {
            if ('' !== $parent) {
                $parent .= '.';
            }
            $parent .= (true === isset($route['prefix'])) ? $route['prefix'] : '';
            $templateString = '';
            $tabs = (true === isset($route['prefix'])) ? (($indent * 3) + 3) : 0;
            if (true === isset($route['custom'])) {
                foreach ($route['custom'] as $custom_key => $custom) {
                    if (true === isset($custom['function']) && '' !== $custom['function']) {
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
                foreach ($route['resources'] as $resource_key => $resource) {
                    if (true === isset($resource['only']) && '*' !== $resource['only']) {
                        $only = '->only([\''.implode('\', \'', explode(',', $resource['only'])).'\'])';
                    } else {
                        $only = '';
                    }

                    $this->classNames[$resource['controller']] = $resource['as'];

                    $vars = [
                        '$ITERATION_MODEL_NAME_PLURAL_CAMEL$' => Str::camel(Str::plural($resource_key)),
                        '$ITERATION_CONTROLLER_NAME_AS$'      => $resource['as'],
                        '$ITERATION_ONLY$'                    => $only,
                        '$INDENT$'                            => infy_tabs($tabs),
                    ];
                    $templateString .= get_artomator_template('scaffold.routes.prefixed.route');
                    $templateString = fill_template($vars, $templateString);
                }
            }
            if (true === (isset($route['group']))) {
                $templateString .= $this->buildText($route['group'], ($indent + 1), $parent);
            }
            if (true === (isset($route['prefix']))) {
                if (true === isset($resource['fallback'])) {
                    $fallback = $resource['fallback'];
                } else {
                    $fallback = '';
                }
                $vars = [
                    '$ITERATION_NAMESPACE_CAMEL$' => ucfirst($route_key),
                    '$ITERATION_NAMESPACE_LOWER$' => strtolower($route_key),
                    '$FALLBACK_ROUTE$'            => $fallback,
                    '$INDENT$'                    => infy_tabs($indent * 3),
                ];
                if ('' !== $fallback) {
                    $fallback_tmp = get_artomator_template('scaffold.routes.prefixed.fallback');
                } else {
                    $fallback_tmp = '';
                }
                $templateString = get_artomator_template('scaffold.routes.prefixed.namespace')
                    .$templateString
                    .$fallback_tmp
                    .get_artomator_template('scaffold.routes.prefixed.closure');

                $templateString = fill_template($vars, $templateString);
            }
            $templateContent .= $templateString;
        }

        return $templateContent;
    }

    /**
     * Build the class names to be used in the refences.
     *
     * @return string The string of classnames in the form.
     */
    private function buildClasses()
    {
        // $this->classNames = Arr::sort($this->classNames);

        $classContent = '';
        foreach ($this->classNames as $controller => $as) {
            if ('' !== $as) {
                $as = ' as '.$as;
            }
            $var = [
                '$ITERATION_NAMESPACE_CONTROLLER_NAME$' => $controller,
                '$ITERATION_NAMESPACE_CONTROLLER_AS$' => $as,
            ];

            $classContent .= get_artomator_template('scaffold.routes.prefixed.reference');

            $classContent = fill_template($var, $classContent);
        }

        return $classContent;
    }
}
