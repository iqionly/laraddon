<?php declare(strict_types=1);

namespace Laraddon\Debugs;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Laraddon\Core;
use Laraddon\Interfaces\Initiable;

class Profiler
{   
    /**
     * Render page for profiler
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return View::make('laraddon::profiler');
    }


}