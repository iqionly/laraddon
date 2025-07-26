<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\View\Factory as ViewFactory;
use Laraddon\Attributes\Routes\HasRoutes;
use Laraddon\Core;

abstract class Registerer
{
    use HasRoutes;

    protected Application $app;
    protected Core $core;

    protected Router $router;
    protected ViewFactory $view;

    public function __construct(Application $app, Core $core) {
        $this->app = $app;
        $this->core = $core;

        $this->router = $app->get(Router::class);
        $this->view = $app->get(ViewFactory::class);

        $this->middleware_groups = $core->middleware_groups;
        $this->generate_api = $core->generate_api;
        $this->excluded_routes = $core->excluded_routes;
    }
}