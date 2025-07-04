<?php

namespace Iqionly\Laraddon\Debugs;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Iqionly\Laraddon\Core;

class Profiler
{
    protected Router $router;
    protected Core $core;

    protected string $route = '_profiler';
    protected RouteCollection $routes;

    public function __construct(Router $router, Core $core)
    {
        $this->router = $router;
        $this->core = $core;
    }

    public function init()
    {
        $this->routes = $this->router->getRoutes();
    }

    public function render()
    {
        return View::make('laraddon::profiler', [
            'routes' => $this->routes
        ]);
    }
}