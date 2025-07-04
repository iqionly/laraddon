<?php

namespace Iqionly\Laraddon\Registerer;

use Illuminate\Container\Container;
use Iqionly\Laraddon\Core;
use Iqionly\Laraddon\Interfaces\Module;

class ViewRegisterer {

    protected Container $app;
    protected Core $core;

    protected bool|string $path_app_addons = '';

    /**
     * @var array<string, string> $list_path_view_modules
     */
    protected array $list_path_view_modules = [];

    /**
     * @var string VIEW_PATH_MODULE
     */
    public const VIEW_PATH_MODULE = 'Views';
    

    public function __construct(Container $app, Core $core) {
        $this->app = $app;
        $this->core = $core;

        $this->path_app_addons = $core->getFoldersAddon();
    }

    public function init(): self {
        $this->listingPathViewModules();

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

        foreach ($this->core->getListAvailableModules() as $value) {
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
}