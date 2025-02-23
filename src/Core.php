<?php

namespace Iqionly\Laraddon;

use Illuminate\Container\Container;
use Composer\Autoload\ClassLoader;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

@include_once __DIR__ . '../vendor/autoload.php';

class Core
{
    protected $base_path;
    protected $app;

    protected array $folders = [
        'addons' => false,
    ];

    protected $list_modules = [];

    public function __construct(Container $app)
    {
        $this->base_path = $app->get('app')->basePath();
        $this->app = $app;
    }

    public function init()
    {
        // Save the state in cache forever
        // $this->app->make('cache')->forever('initialize', true);

        // Check folder addons exist
        if($this->checkFolderAddons($this->base_path . '/addons')) {
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

    private function loadModules() {
        // Load all modules
        $this->list_modules = array_diff(
            scandir($this->folders['addons'], SCANDIR_SORT_NONE),
            ['.', '..']
        );

        $this->list_modules = array_values($this->list_modules);

        $loader = new ClassLoader($this->folders['addons']);
        $class_maps = [];
        foreach ($this->list_modules as $module) {
            $normalized_name = Str::slug($module);
            $loader->addPsr4($module . '\\', $this->folders['addons'] . '/' . $normalized_name);
            $class_maps[] = [
                $module . '\\' => $this->folders['addons'] . '/' . $normalized_name
            ];
        }

        $loader->addClassMap($class_maps);
        unset($class_maps);
        $loader->register();

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
}