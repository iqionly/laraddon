<?php

declare(strict_types=1);

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;
use Composer\Autoload\ClassLoader;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

use Iqionly\Laraddon\Bus\Module;

@include_once __DIR__ . '../vendor/autoload.php';

class Core
{
    protected string $addons_path;
    protected $app;

    protected array $folders = [
        'addons' => false,
    ];

    protected $list_modules = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->addons_path = $app->get('config')->get('laraddon.addons_path');
    }

    public function init()
    {
        // Save the state in cache forever
        // $this->app->make('cache')->forever('initialize', true);

        // Check folder addons exist
        if($this->checkFolderAddons($this->addons_path)) {
            $this->loadModules();
        }

        return $this;
    }

    /**
     * Check if folder addons exist, if not creating
     *
     * @param string $path
     * 
     * @return bool
     * 
     */
    private function checkFolderAddons(string $path) {
        if(!is_dir($path)) {
            mkdir($path, 0700, true);
        }

        $this->folders['addons'] = $path;

        return true;
    }

    public function getFoldersAddon() {
        if(!empty($this->folders['addons'])) {
            return $this->folders['addons'];
        }

        $this->init();

        return $this->folders['addons'];
    }

    /**
     * Returns a list of available modules.
     * 
     * @return array<Module>
     */
    public function getListAvailableModules()
    {
        if(!empty($this->list_modules)) {
            return $this->list_modules;
        }

        $this->list_modules = array_diff(
            scandir($this->folders['addons'], SCANDIR_SORT_NONE),
            ['.', '..']
        );

        $this->list_modules = array_values($this->list_modules);

        return $this->list_modules;
    }

    private function loadModules() {
        // Get list available module
        $this->getListAvailableModules();

        // Load all modules
        $loader = new ClassLoader($this->folders['addons']);
        
        $class_maps = [];
        foreach ($this->list_modules as $module) {
            $normalized_name = Str::slug($module);
            $loader->addPsr4($module . '\\', $this->folders['addons'] . '/' . $normalized_name);
            $class_maps = [
                $module . '\\' => $this->folders['addons'] . '/' . $normalized_name
            ];
        }

        $loader->addClassMap($class_maps);
        unset($class_maps);
        $loader->register();

        $this->list_modules = [];
        foreach ($loader->getClassMap() as $class => $path) {
            $this->list_modules[] = new Module($class, $path);
        }

        return $this->list_modules;
    }

    public static function getListModules() {
        return App::get(self::class)->list_modules;
    }

    public static function getFolders() {
        return App::get(self::class)->folders;
    }

    public static function camelToUnderscore($string, $us = "_") {
        return strtolower(preg_replace('/(?<!^)[A-Z]+|(?<!^|\d)[\d]+/', $us.'$0', $string));
    }

    public static function removeParenthesis($string) {
        return preg_replace('/[\(\)\{\}\[\]]+/', '', $string);
    }
}