<?php declare(strict_types=1);

namespace Laraddon\Debugs;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Laraddon\Core;
use Laraddon\Interfaces\Initiable;

class Profiler implements Initiable
{
    protected Router $router;
    protected Core $core;

    protected string $route = '_profiler';
    protected RouteCollectionInterface $routes;

    public function __construct(Router $router, Core $core)
    {
        $this->router = $router;
        $this->core = $core;
    }
    
    /**
     * Initialize Class
     *
     * @return self
     */
    public function init(): self
    {
        $this->routes = $this->router->getRoutes();

        return $this;
    }
    
    /**
     * Render page for profiler
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $grouping = [];
        foreach ($this->routes as $route) {
            $mapped = [];
            $uriParts = explode('/', $route->uri());
            foreach($uriParts as $uriPart) {
                $mapped[$uriPart] = [];
            }
        }
        return View::make('laraddon::profiler', [
            'routes' => $this->routes
        ]);
    }


}