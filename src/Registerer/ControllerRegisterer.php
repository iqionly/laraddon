<?php

namespace Iqionly\Laraddon\Registerer;

use Illuminate\Container\Container;
use Iqionly\Laraddon\Core;

class ControllerRegisterer {

    protected Container $app;
    protected Core $core;

    public const string CONTROLLER_PATH_MODULE = 'Controllers';

    public function __construct(Container $app, Core $core) {
        $this->app = $app;
        $this->core = $core;
    }

    public function init() {
        return $this;
    }
}