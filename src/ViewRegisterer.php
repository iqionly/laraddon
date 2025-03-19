<?php

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;

class ViewRegisterer {

    protected Core $core;

    protected string $path_app_addons = '';
    protected array $list_path_view_modules = [];

    public const VIEW_PATH_MODULE = 'Views';
    

    public function __construct(Container $app, Core $core) {
        $this->core = $core;

        $this->path_app_addons = $core->getFoldersAddon();
    }

    public function init() {
        $this->listingPathViewModules();

        return $this;
    }

    private function listingPathViewModules() {
        if(!empty($this->list_path_view_modules)) {
            return $this->list_path_view_modules;
        }

        foreach ($this->core->getListAvailableModules() as $value) {
            $this->list_path_view_modules[$value] = $this->path_app_addons . "/$value/". static::VIEW_PATH_MODULE;
        }

        return $this->list_path_view_modules;
    }

    public function listPathViewModules() {
        return $this->list_path_view_modules;
    }
}