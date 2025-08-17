<?php

namespace Laraddon\Views\Components;

use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Laraddon\Core;

class ListRoutes extends Component
{
    protected Router $router;
    protected Core $core;

    protected string $route = '_profiler';
    protected RouteCollectionInterface $routes;

    public function __construct(Router $router, Core $core)
    {
        $this->router = $router;
        $this->core = $core;
        $this->routes = $this->router->getRoutes();
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return View::make('laraddon::components.list-routes', [
            'routes' => $this->routes
        ]);
    }
}
