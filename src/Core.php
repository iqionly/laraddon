<?php

declare(strict_types=1);

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;
use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

use Iqionly\Laraddon\Bus\Module;
use ReflectionClass;
use ReflectionMethod;

@include_once __DIR__ . '../vendor/autoload.php';

class Core
{
    protected Container $app;

    protected string $addons_path;
    protected string $addons_name;
    /**
     * @var array<string, bool|string> $folders
     */
    protected array $folders = [
        'addons' => false,
    ];
    /**
     * @var array<int, string> $list_modules
     */
    protected array $list_modules = [];

    
    /**
     * This is move to Core because many classes need to access this
     * @var array<int, string> $middleware_groups
     */
    public static array $middleware_groups = [];
    public static bool $generate_api = false;
    /**
     * @var array<int, string> $excluded_routes
     */
    public static array $excluded_routes = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
        /** @var Application $foundation */
        $foundation = $app->get('app');
        /** @var Repository $config */
        $config = $app->get('config');
        $this->addons_path = $foundation->basePath($config->get('laraddon.addons_path'));
        $this->addons_name = ucwords(basename($this->addons_path)); // Different from consumer class, we just use Laraddon/Loaded
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
        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $this->app->get(Kernel::class);

        self::$middleware_groups = array_keys($kernel->getMiddlewareGroups());
        self::$generate_api = $this->app->get('config')->get('laraddon.api_routes');

        if(!self::$generate_api) {
            self::$middleware_groups = array_filter(self::$middleware_groups, function ($val) {
                return $val != 'api';
            });
        }
        self::$excluded_routes = self::setExludedRoutes();

        // Exlude default routes abtract laravel controller 
        self::$middleware_groups = array_filter(self::$middleware_groups, function ($val){
            return !in_array($val, self::$excluded_routes);
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
     * @return array
     */
    public static function setExludedRoutes(): array {
        $laravelController = new ReflectionClass(\Illuminate\Routing\Controller::class);
        $methods = $laravelController->getMethods(ReflectionMethod::IS_PUBLIC);
        $excluded_routes = [];
        foreach ($methods as $method) {
            $excluded_routes[] = $method->getName();
        }
        return $excluded_routes;
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
     * @return array<Module> $list_modules
     */
    public function getListAvailableModules()
    {
        if(!empty($this->list_modules)) {
            return $this->list_modules;
        }

        $this->list_modules = array_diff(
            scandir($this->folders['addons'], SCANDIR_SORT_NONE),
            ['.', '..']
        );

        $this->list_modules = array_values($this->list_modules);

        return $this->list_modules;
    }

    private function loadModules() {
        // Get list available module
        $this->getListAvailableModules();

        // Load all modules
        $loader = new ClassLoader($this->folders['addons']);
        
        $class_maps = [];
        foreach ($this->list_modules as $module) {
            $normalized_name = Str::slug($module);
            $loader->addPsr4($this->addons_name . '\\' . $module . '\\', $this->folders['addons'] . '/' . $normalized_name);
            $class_maps = [
                $this->addons_name . '\\' . $module . '\\' => $this->folders['addons'] . '/' . $normalized_name
            ];
        }

        $loader->addClassMap($class_maps);
        unset($class_maps);

        $this->list_modules = [];
        foreach ($loader->getClassMap() as $class => $path) {
            $this->list_modules[] = new Module($class, $path);
        }

        return $this->list_modules;
    }
    
    /**
     * Get listed module
     *
     * @return array<Module>
     */
    public static function getListModules() {
        return App::get(self::class)->list_modules;
    }

    public static function getFolders() {
        return App::get(self::class)->folders;
    }

    public static function camelToUnderscore($string, $us = "_") {
        return strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', $us.'$0', $string));
    }

    public static function removeParenthesis($string) {
        return preg_replace('/[\(\)\{\}\[\]]+/', '', $string);
    }
}