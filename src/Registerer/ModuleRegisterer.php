<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use Laraddon\Bus\Module;
use Laraddon\Core;
use Laraddon\Interfaces\Initiable;

class ModuleRegisterer implements Initiable {
    protected Core $core;

    public function __construct(Core $core)
    {
        $this->core = $core;
    }


    public function init(): self {
        $this->mappModels($this->core->getListModules());
        return $this;
    }

    /**
     * @param array<int,Module> $modules
     * 
     * @return void
     */
    private function mappModels(array $modules) {
        foreach ($modules as $module) {
            // dd($module);
        }
    }
}