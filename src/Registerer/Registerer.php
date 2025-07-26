<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Laraddon\Attributes\Routes\HasRoutes;
use Laraddon\Core;

abstract class Registerer
{
    use HasRoutes;

    protected Application $app;
    protected Core $core;

    protected \Illuminate\Routing\Router $router;
    protected \Illuminate\View\Factory $view;

    public function __construct(Application $app, Core $core) {
        $this->app = $app;
        $this->core = $core;

        $this->router = $app->get('router');
        $this->view = $app->get('view');

        $this->middleware_groups = $core->middleware_groups;
        $this->generate_api = $core->generate_api;
        $this->excluded_routes = $core->excluded_routes;
    }
}