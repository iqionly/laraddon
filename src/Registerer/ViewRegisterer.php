<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use Error;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\View\ViewFinderInterface;
use Laraddon\Attributes\Routes\HasRoutes;
use Laraddon\Core;
use Laraddon\Errors\InvalidModules;

class ViewRegisterer extends Registerer {

    use HasRoutes;

    /**
     * @var array<string, string> $list_path_view_modules
     */
    protected array $list_path_view_modules = [];

    /**
     * @var string VIEW_PATH_MODULE
     */
    public const VIEW_PATH_MODULE = 'Views';

    public function init(): self {
        $this->listingPathViewModules();
        $this->extendView();

        return $this;
    }
    
    /**
     * listingPathViewModules
     *
     * @return array<string, string> $list_path_view_modules
     */
    private function listingPathViewModules(): array {
        if(!empty($this->list_path_view_modules)) {
            return $this->list_path_view_modules;
        }

        foreach ($this->core->getListModules() as $value) {
            $this->list_path_view_modules[(string) $value] = $value->getPath() . "/" . static::VIEW_PATH_MODULE;
        }

        return $this->list_path_view_modules;
    }

        
    /**
     * @inheritdoc Iqionly\Laraddon\Registerer\ViewRegisterer::listingPathViewModules
     * 
     * @return array<string, string>
     */
    public function listPathViewModules(): array {
        return $this->listingPathViewModules();
    }

    /**
     * Extend View Finder to register views from addons
     *
     * @return void
     */
    public function extendView(): void
    {
        $this->app->extend($this->view::class, function (\Illuminate\View\Factory $view) {
            // This is temporary to register views of base addon
            // We need to auto add location base of addons folder listed
            // we don't put this in config file, read it from database and cached to php files

            $registerer = $this->app->get(ViewRegisterer::class);
            foreach ($registerer->listPathViewModules() as $key => $value) {
                $view->getFinder()->addLocation($value);
            }
        });
    }

    public function registerRoute($value) {
        $path = $value->getPath() . '/' . ViewRegisterer::VIEW_PATH_MODULE;
        if(is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $file = str_replace('.blade.php', '', $file);
                $routePath = "/" . Core::camelToUnderscore($file, '-');
                if($file == "index") {
                    $routePath = '';
                }
                $route = $this->router->addRoute(Router::$verbs[0], $value . $routePath, function (...$args) use ($file) {
                    return $this->view->make(Core::camelToUnderscore($file, '-'), $args);
                });
                $route->name($value->getName() . '.' . Core::camelToUnderscore($file, '-'));
            }
        } else {
            throw new InvalidModules("Views folder not found in $value", 12001);
        }
    }
}