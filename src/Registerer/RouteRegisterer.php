<?php declare(strict_types=1);

namespace Iqionly\Laraddon\Registerer;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Iqionly\Laraddon\Attributes\HasRoutes;
use Iqionly\Laraddon\Core;
use Iqionly\Laraddon\Errors\InvalidModules;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class RouteRegisterer
{
    use HasRoutes;
    
    protected Core $core;
    protected ControllerRegisterer $controller_registerer;

    protected \Illuminate\Routing\Router $router;
    protected \Illuminate\View\Factory $view;
    

    public function __construct(Container $app, Core $core)
    {
        
        $this->core = $core;
        $config = $app->get('config');
        $this->router = $app->get('router');
        $this->view = $app->get('view');

        $this->middleware_groups = $core->middleware_groups;
        $this->excluded_routes = $core->excluded_routes;
        $this->controller_registerer = $app->get(ControllerRegisterer::class);
    }

    public function init(): self {
        $this->registerRoutes();

        return $this;
    }
    
    /**
     * Register all route views, controllers, models
     *
     * @return void
     */
    public function registerRoutes(): void {
        $list_modules = Core::getListModules();
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
            $path = $value->getPath() . '/' . ViewRegisterer::VIEW_PATH_MODULE;
            if(is_dir($path)) {
                $files = array_diff(scandir($path), ['.', '..']);
                foreach ($files as $file) {
                    $file = str_replace('.blade.php', '', $file);
                    $routePath = "/" . Core::camelToUnderscore($file, '-');
                    if($file == "index") {
                        $routePath = '';
                    }
                    $route = $this->router->addRoute(Router::$verbs[0], $value . $routePath , function (...$args) use ($file) {
                        return $this->view->make(Core::camelToUnderscore($file, '-'), $args);
                    });
                    $route->name($value->getName() . '.' . Core::camelToUnderscore($file, '-'));
                }
            } else {
                throw new InvalidModules("Views folder not found in $value", 12001);
            }

            /**
             * Step 2.
             */
            $this->controller_registerer->registerRoute($value);
            
            /**
             * Step 3.
             */

        }
    }
}