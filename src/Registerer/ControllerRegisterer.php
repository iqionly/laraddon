<?php

namespace Iqionly\Laraddon\Registerer;

use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Iqionly\Laraddon\Core;
use Iqionly\Laraddon\Errors\InvalidModules;
use Iqionly\Laraddon\Interfaces\Module;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class ControllerRegisterer {

    public const string CONTROLLER_PATH_MODULE = 'Controllers';

    protected Container $app;
    protected Core $core;

    protected \Illuminate\Routing\Router $router;
    protected \Illuminate\View\Factory $view;

    protected array $middleware_groups;
    protected bool $generate_api;
    protected array $excluded_routes;

    public function __construct(Container $app, Core $core) {
        $this->app = $app;
        $this->core = $core;

        $this->router = $app->get('router');
        $this->view = $app->get('view');

        $this->middleware_groups = Core::$middleware_groups;
        $this->generate_api = Core::$generate_api;
        $this->excluded_routes = Core::$excluded_routes;
    }

    public function init() {
        return $this;
    }

    public function registerRoute(Module &$module) {

        // Check if setting global generate api is true, and check scope module use api or not
        if($this->generate_api && !$module->getApiRoutesAttribute()) {
            $this->middleware_groups = array_filter($this->middleware_groups, function ($val) {
                return $val != 'api';
            });
        }

        $path = $module->getPath() . '/' . self::CONTROLLER_PATH_MODULE;
        if(is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $file = str_replace('.php', '', $file);
                $modulePath = $module->getClass() . '\\' . self::CONTROLLER_PATH_MODULE . '\\' . $file;
                if(class_exists($modulePath)) {
                    return $this->extractRoute($module->getName(), new \ReflectionClass($modulePath), $module);
                }
            }
        } else {
            throw new InvalidModules("Views folder not found in $module", 12001);
        }
    }

    private function extractRoute(string $name, \ReflectionClass $reflect, Module &$module) {
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
                elseif($type == null && $param instanceof ReflectionParameter)
                    $uri .= "/{" . strtolower($param->getName()) . "}";
                
            }
            
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