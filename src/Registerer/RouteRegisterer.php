<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Laraddon\Attributes\Routes\HasRoutes;
use Laraddon\Core;

class RouteRegisterer extends Registerer
{
    use HasRoutes;
    
    private ControllerRegisterer $controller_registerer;
    private ViewRegisterer $view_registerer;

    public function __construct(Application $app, Core $core)
    {
        parent::__construct($app, $core);

        $this->controller_registerer = $app->get(ControllerRegisterer::class);
        $this->view_registerer = $app->get(ViewRegisterer::class);
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
            $this->view_registerer->registerRoute($value);

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