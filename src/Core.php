<?php declare(strict_types=1);

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;
use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
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
        $router = $this->app->get(Router::class);

        $this->middleware_groups = array_keys($router->getMiddlewareGroups());
        $this->generate_api = $this->app->get('config')->get('laraddon.api_routes');

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
        $loader = new ClassLoader($this->folders['addons']);
        
        $class_maps = [];
        foreach ($list_modules as $module) {
            $normalized_name = Str::slug($module);
            $loader->addPsr4($this->addons_name . '\\' . $module . '\\', $this->folders['addons'] . '/' . $normalized_name);
            $class_maps = [
                $this->addons_name . '\\' . $module . '\\' => $this->folders['addons'] . '/' . $normalized_name
            ];
        }

        $loader->addClassMap($class_maps);
        unset($class_maps);

        foreach ($loader->getClassMap() as $class => $path) {
            $this->list_modules[] = new Module($class, $path);
        }

        return $this->list_modules;
    }
    
    /**
     * Get listed module
     *
     * @return array<int, Module> $list_modules
     */
    public static function getListModules() {
        return App::get(self::class)->list_modules;
    }
    
    /**
     * Get All Folders Available in module client
     *
     * @return array<string, bool|string> $folders
     */
    public static function getFolders() {
        return App::get(self::class)->folders;
    }
    
    /**
     * camelToUnderscore
     *
     * @param  string $string
     * @param  string $us
     * @return string
     */
    public static function camelToUnderscore(string $string, string $us = "_") {
        return strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', $us.'$0', $string));
    }
    
    /**
     * removeParenthesis
     *
     * @param  string $string
     * @return string
     */
    public static function removeParenthesis($string) {
        return preg_replace('/[\(\)\{\}\[\]]+/', '', $string);
    }
}