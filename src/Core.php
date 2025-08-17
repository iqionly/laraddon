<?php declare(strict_types=1);

namespace Laraddon;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Laraddon\Bus\Module;
use Laraddon\Interfaces\Initiable;

use ReflectionClass;
use ReflectionMethod;

class Core implements Initiable
{
    protected Application $app;

    protected string $addons_path;
    protected string $addons_name;

    /**
     * @var array<string, string> $folders
     */
    protected array $folders = [
        'addons' => '',
    ];

    /**
     * @var array<int, Module> $list_modules
     */
    protected array $list_modules = [];

    
    /**
     * This is move to Core because many classes need to access this
     * @var array<int, string> $middleware_groups
     */
    public array $middleware_groups = [];
    public bool $generate_api = false;
    /**
     * @var array<int, string> $excluded_routes
     */
    public array $excluded_routes = [];

    /**
     * @param Application $app
     * 
     * @throws \Exception
     * 
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        /** @var Repository $config */
        $config = $app->get('config');
        /** @var Router $router */
        $router = $this->app->get(Router::class);
        $config_addon_path = $config->get('laraddon.addons_path');
        $config_api_routes = $config->get('laraddon.api_routes');

        if(!is_string($config_addon_path)) {
            throw new \Exception("Configuration 'laraddon.addons_path' must be a string.", 10050);
        }

        if(!is_bool($config_api_routes)) {
            throw new \Exception("Configuration 'laraddon.api_routes' must be a boolean.", 10051);
        }
        $this->generate_api = $config_api_routes;
        $this->addons_path = $this->app->basePath($config_addon_path);
        $this->addons_name = ucwords(basename($this->addons_path)); // Different from consumer class, we just use Laraddon/Loaded
        $this->middleware_groups = array_keys($router->getMiddlewareGroups());
    }

    public function init(): self
    {
        $this->setRouteVariables();

        // Check folder addons exist
        if($this->checkFolderAddons($this->addons_path)) {
            $this->loadModules();
        }

        return $this;
    }

    private function setRouteVariables(): void {
        if(!$this->generate_api) {
            $this->middleware_groups = array_filter($this->middleware_groups, function ($val) {
                return $val != 'api';
            });
        }
        $this->excluded_routes = $this->setExludedRoutes();

        // Exlude default routes abtract laravel controller 
        $this->middleware_groups = array_filter($this->middleware_groups, function ($val){
            return !in_array($val, $this->excluded_routes);
        });
    }

    /**
     * Sets the excluded routes by retrieving all public methods
     * from the base Laravel Controller class and adding their names
     * to the `$excluded_routes` property.
     *
     * This ensures that default Laravel controller methods are excluded
     * from being registered as routes.
     *
     * @return array<int, string> $excluded_routes
     */
    public function setExludedRoutes(): array {
        $laravelController = new ReflectionClass(\Illuminate\Routing\Controller::class);
        $methods = $laravelController->getMethods(ReflectionMethod::IS_PUBLIC);
        $excluded_routes = [];
        foreach ($methods as $method) {
            $excluded_routes[] = $method->getName();
        }
        return $excluded_routes;
    }


    public function getFoldersAddon(): bool|string {
        if(!empty($this->folders['addons'])) {
            return $this->folders['addons'];
        }

        $this->init();

        return $this->folders['addons'];
    }

    /**
     * Returns a list of available modules.
     * 
     * @return array<int, string> $list_modules
     */
    public function getListAvailableModules()
    {
        $list_modules = array_diff(
            scandir($this->folders['addons'], SCANDIR_SORT_NONE),
            ['.', '..']
        );

        $list_modules = array_values($list_modules);

        return $list_modules;
    }
    
    /**
     * Get listed module
     * 
     * @throws \Exception
     *
     * @return array<int, Module> $list_modules
     */
    public static function getListModules() {
        $result = App::get(self::class);
        if(!$result instanceof self) {
            throw new \Exception("Core class not initialized properly.", 10052);
        }
        return $result->list_modules;
    }
    
    /**
     * Get All Folders Available in module client
     *
     * @throws \Exception
     * 
     * @return array<string, bool|string> $folders
     */
    public static function getFolders() {
        $result = App::get(self::class);
        if(!$result instanceof self) {
            throw new \Exception("Core class not initialized properly.", 10053);
        }
        return $result->folders;
    }

    /**
     * Get the Application instance.
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Load and initialize all modules if not loaded.
     *
     * @return array<int, Module> $list_modules
     */
    private function loadModules() {
        if(!empty($this->list_modules)) {
            return $this->list_modules;
        }

        // Get list available module
        $list_modules = $this->getListAvailableModules();

        
        // Load all modules
        $loader = new \Composer\Autoload\ClassLoader($this->folders['addons']);
        
        $class_maps = [];
        foreach ($list_modules as $module) {
            $loader->addPsr4($this->addons_name . '\\' . $module . '\\', $this->folders['addons'] . '/' . $module);
            $class_maps[$this->addons_name . '\\' . $module . '\\'] = $this->folders['addons'] . '/' . $module;
        }
        
        $loader->addClassMap($class_maps);
        unset($class_maps);
        
        foreach ($loader->getClassMap() as $class => $path) {
            $this->list_modules[] = new Module($class, $path);
        }

        return $this->list_modules;
    }
    
    /**
     * camelToUnderscore
     *
     * @param  string $string
     * @param  string $us
     * @return string $string
     */
    public static function camelToUnderscore(string $string, string $us = "_") {
        // Change backslash to slash
        $string = str_replace('\\', '/', $string);
    
        // explode the slash to part
        $parts = explode('/', $string);
        foreach ($parts as &$part) {
            // replace CamelCase to $us param
            $replace = preg_replace('/(?<!^)([A-Z])/', $us.'$1', $part);
            if(!is_string($replace)) {
                return $part;
            }
            $part = strtolower($replace);
            // If fail return origin string
            if($part == null) {
                return $string;
            }
        }
    
        // Union the arrat parts
        return implode('/', $parts);
    }
    
    /**
     * removeParenthesis
     *
     * @param  string $string
     * @return string
     */
    public static function removeParenthesis($string) {
        if($replaced = preg_replace('/[\(\)\{\}\[\]]+/', '', $string)) {
            return $replaced;
        }
        return $string;
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
}