<?php

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;
use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Str;
use ReflectionMethod;

@include_once __DIR__ . '../vendor/autoload.php';

class Core
{
    protected $base_path;
    protected $app;
    protected \Illuminate\Routing\Router $router;
    protected array $middlewareGroups = [];

    protected $folders = [
        'addons' => false,
    ];

    protected $list_modules = [];

    public function __construct(Container $app)
    {
        $this->base_path = $app->get('app')->basePath();
        $this->app = $app;
        $this->router = $app->get('router');
        $this->middlewareGroups = array_keys($this->app->make(Kernel::class)->getMiddlewareGroups()); // P.S : This is not the best way to get middleware groups, so slow -__-
    }

    public function init()
    {
        // Save the state in cache forever
        // $this->app->make('cache')->forever('initialize', true);

        // Check folder addons exist
        if($this->checkFolderAddons($this->base_path . '/addons')) {
            $this->loadModules();
        }

        return $this;
    }

    /**
     * Check if folder addons exist, if not creating
     *
     * @param string $path
     * 
     * @return bool
     * 
     */
    private function checkFolderAddons(string $path) {
        if(!is_dir($path)) {
            mkdir($path, 0700, true);
        }

        $this->folders['addons'] = $path;

        return true;
    }

    private function loadModules() {
        // Load all modules
        $this->list_modules = array_diff(
            scandir($this->folders['addons'], SCANDIR_SORT_NONE),
            ['.', '..']
        );

        $this->list_modules = array_values($this->list_modules);

        $loader = new ClassLoader($this->folders['addons']);
        $class_maps = [];
        foreach ($this->list_modules as $module) {
            $normalized_name = Str::slug($module);
            $loader->addPsr4($module . '\\', $this->folders['addons'] . '/' . $normalized_name);
            $class_maps[] = [
                $module . '\\' => $this->folders['addons'] . '/' . $normalized_name
            ];
        }

        $loader->addClassMap($class_maps);
        unset($class_maps);
        $loader->register();
    }

    public function registerRoutes() {
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
        foreach ($this->list_modules as $value) {
            $data = require $this->folders['addons'] . '/' . $value . '/init.php';
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

    private function camelToUnderscore($string, $us = "_") {
        return strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', $us.'$0', $string));
    }

    private function extractRoute(string $name, \ReflectionClass $reflect) {
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $parameters = $method->getParameters();
            $name_method = $method->getName();
            $attributes = $method->getAttributes();
            if(count($attributes) == 0) {
                $attribute = [ 'get' => $method->getName()];
            } else {
                $attribute = isset($attributes[0]) ? $attributes[0]->getArguments() : null;
            }
            if(!$attribute) {
                continue;
            }
            $method = array_key_first($attribute);
            $uri = $attribute[$method];
            
            foreach ($this->middlewareGroups as $group) {
                // Detect if projects using default middleware api, we will add prefix api
                $result = [
                    strtoupper($method),
                    $this->camelToUnderscore($name, '-') . '/' . $uri,
                    [$reflect->getName(), $uri]
                ];
                if($group == 'api') {
                    // Add prefix in first, because if we use method prefix(), it will be added in uri also, and we don't want
                    $result[2]['prefix'] = $group;
                }
                $route = $this->router->addRoute(...$result)->middleware($group);
                $route_name = $group == 'api' ? 'api.' : '';
                $route_name .= $this->camelToUnderscore($name, '-') . '.' . $this->camelToUnderscore($name_method, '-');
                $route->name($route_name);
            }
        }
    }
}