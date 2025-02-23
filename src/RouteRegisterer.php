<?php

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use ReflectionMethod;
use ReflectionNamedType;

class RouteRegisterer
{
    protected \Illuminate\Routing\Router $router;
    protected array $middleware_groups = [];

    protected bool $generate_api = false;

    public function __construct(Container $app)
    {
        $config = $app->get('config');
        $this->generate_api = $config->get('laraddon.api_routes');
        $this->router = $app->get('router');
        $this->middleware_groups = array_keys($app->make(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()); // P.S : This is not the best way to get middleware groups, so slow -__-
    }

    public function init() {
        $this->registerRoutes();
    }

    public function registerRoutes() {
        $list_modules = Core::getListModules();
        $folders = Core::getFolders();
        // 
        // Example use addRoute
        // 
        // $this->router->addRoute('GET', 'test', [
        //     'App\Http\Controllers\BasicController',
        //     'index',
        //     'prefix' => 'api'
        // ])->middleware('web');
        // dd($this->router->getRoutes());
        // Get list all modules files
        // This section we will register route from controllers, and put in cached route laravel
        foreach ($list_modules as $value) {
            $data = require $folders['addons'] . '/' . $value . '/init.php';
            foreach ($data as $key => $item) {
                if($key == 'controllers') {
                    foreach ($item as $controller) {
                        $reflect = new \ReflectionClass($controller);
                        $this->extractRoute($value, $reflect);
                    }
                }
            }
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
            
            foreach ($this->middleware_groups as $group) {
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
                $route->name($route_name);
            }
        }
    }
}