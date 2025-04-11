<?php

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Iqionly\Laraddon\Errors\InitFileNotFound;
use Iqionly\Laraddon\Errors\InvalidModules;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class RouteRegisterer
{
    protected \Illuminate\Routing\Router $router;
    protected array $middleware_groups = [];

    protected bool $generate_api = false;

    protected Core $core;

    protected array $excluded_routes = [];

    public function __construct(Container $app, Core $core)
    {
        $this->core = $core;
        $config = $app->get('config');
        $this->generate_api = $config->get('laraddon.api_routes');
        $this->router = $app->get('router');
        $this->middleware_groups = array_keys($app->get(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()); // P.S : This is not the best way to get middleware groups, so slow -__-

        $this->setExludedRoutes();
    }

    public function init() {
        $this->registerRoutes();

        return $this;
    }

    /**
     * Sets the excluded routes by retrieving all public methods
     * from the base Laravel Controller class and adding their names
     * to the `$excluded_routes` property.
     *
     * This ensures that default Laravel controller methods are excluded
     * from being registered as routes.
     *
     * @return void
     */
    public function setExludedRoutes() {
        $laravelController = new ReflectionClass(\Illuminate\Routing\Controller::class);
        $methods = $laravelController->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $this->excluded_routes[] = $method->getName();
        }
    }

    public function registerRoutes() {
        $list_modules = Core::getListModules();
        $folders = Core::getFolders();
        // Get List Modules
        foreach ($list_modules as $value) {
            /**
             * First thing first!
             * the priority route generated from modules based on.
             * 1. Views
             *      Check if Views folder and all file exists, than register right away, based on addon name folder and file name.
             *      ex: we have addon MyAddon in folder so the folder will suspect to be addons/MyAddon/Views/base.blade.php
             *          and generated route will be localhost:8000/my-addon/base.
             * 2. Controllers
             *      Check if init file exists in modules folders.
             *      So we can get all controllers and register it.
             *      This base on the attributes first, if it not exists then method name.
             * 3. Models
             *      Check if Models folder exists, than we can get all models and register it.
             */

            /**
             * Step 1.
             */
            $path = $folders['addons'] . '/' . $value . '/' . ViewRegisterer::VIEW_PATH_MODULE;
            if(is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if($file == '.' || $file == '..') continue;
                    $file = str_replace('.blade.php', '', $file);
                    $route = $this->router->addRoute(Router::$verbs[0], Core::camelToUnderscore($value, '-') . '/' . Core::camelToUnderscore($file, '-'), function () use ($file) {
                        return view(Core::camelToUnderscore($file, '-'));
                    });
                    $route->name(Core::camelToUnderscore($value, '-') . '.' . Core::camelToUnderscore($file, '-'));
                }
            } else {
                throw new InvalidModules("Views folder not found in $value", 12001);
            }

            /**
             * Step 2.
             */
            try {
                $data = require $folders['addons'] . '/' . $value . '/init.php';
            } catch (\ErrorException $e) {
                throw new InitFileNotFound("Init.php file not found in $value", 12002);
            }
            foreach ($data as $key => $item) {
                if($key == 'controllers') {
                    foreach ($item as $controller) {
                        $reflect = new \ReflectionClass($controller);
                        $this->extractRoute($value, $reflect);
                    }
                }
            }

            /**
             * Step 3.
             */

        }
    }

    private function extractRoute(string $name, \ReflectionClass $reflect) {
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $parameters = $method->getParameters();
            $name_method = $method->getName();
            $attributes = $method->getAttributes();
            if(count($attributes) == 0) {
                $attribute = [ 'get' => $name_method ];
            } else {
                $attribute = isset($attributes[0]) ? $attributes[0]->getArguments() : null;
            }
            
            $method = array_key_first($attribute);
            $uri = str_replace('_', '-', $attribute[$method]);

            foreach ($parameters as $param) {
                $type = $param->getType();
                if($type instanceof ReflectionNamedType && $type->getName() != Request::class)
                    $uri .= "/{" . strtolower($param->getName()) . "}";
            }

            if(!$this->generate_api) {
                $this->middleware_groups = array_filter($this->middleware_groups, function ($val) {
                    return $val != 'api';
                });
            }

            // Exlude default routes abtract laravel controller 
            $this->middleware_groups = array_filter($this->middleware_groups, function ($val) use ($name_method) {
                return !in_array($name_method, $this->excluded_routes);
            });
            
            foreach ($this->middleware_groups as $groupkey => $group) {
                // Detect if projects using default middleware api, we will add prefix api
                $result_method = strtoupper($method);
                if($method == 'any') {
                    $result_method = Router::$verbs;
                }
                $result = [
                    $result_method,
                    Core::camelToUnderscore($name, '-') . '/' . $uri,
                    [$reflect->getName(), $name_method]
                ];
                if($group == 'api') {
                    // Add prefix in first, because if we use method prefix(), it will be added in uri also, and we don't want
                    $result[2]['prefix'] = $group;
                }
                $route = $this->router->addRoute(...$result)->middleware($group);
                $route_name = $group == 'api' ? 'api.' : '';
                $route_name .= Core::camelToUnderscore($name, '-') . '.' . Core::camelToUnderscore($uri, '-');
                $route_name = str_replace('/', '.', Core::removeParenthesis($route_name));
                $route->name($route_name);
            }
        }
    }
}