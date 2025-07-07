<?php declare(strict_types=1);

namespace Iqionly\Laraddon\Debugs;

use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Iqionly\Laraddon\Core;

class Profiler
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
     * @return void
     */
    public function init(): void
    {
        $this->routes = $this->router->getRoutes();
    }
    
    /**
     * Render page for profiler
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return View::make('laraddon::profiler', [
            'routes' => $this->routes
        ]);
    }
}