<?php

namespace Iqionly\Laraddon;

use Illuminate\Container\Container as ContainerContainer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\View\ViewFinderInterface;
use Iqionly\Laraddon\Addons\Base\Models\TestModel;
use Iqionly\Laraddon\Interfaces\Migrations\Type;
use Iqionly\Laraddon\Interfaces\ModelBlueprint;
use Iqionly\Laraddon\Interfaces\ModelMigrate;

class LaraddonServiceProvider extends ServiceProvider
{
    private array $classes = [
        \Iqionly\Laraddon\RouteRegisterer::class,
        \Iqionly\Laraddon\ViewRegisterer::class,
    ];

    public function register(): void
    {
        $this->app->singleton(Core::class, function ($app) {
            return new Core($app);
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/laraddon.php', 'laraddon');
        
        $this->registerClasses();
    }
    
    public function boot(): void
    {
        $this->initCore();

        foreach ($this->classes as $class) {
            $this->app->get($class)->init();
        }

        $this->app->extend('view.finder', function (ViewFinderInterface $file_view_finder) {
            // This is temporary to register views of base addon
            // We need to auto add location base of addons folder listed
            // we don't put this in config file, read it from database and cached to php files

            $registerer = $this->viewRegisterer();
            foreach ($registerer->listPathViewModules() as $key => $value) {
                $file_view_finder->addLocation($value);
            }
            $file_view_finder->addLocation(__DIR__ . '/addons/base/views');
            return $file_view_finder;
        });

        $this->app->get(Core::class)->testModelResolver();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [Core::class];
    }

    private function registerClasses()
    {
        foreach ($this->classes as $class) {
            $this->app->singleton($class, function ($app) use ($class) {
                return new $class($app, $app->get(Core::class));
            });
        }
    }

    /**
     * Load All Kernels and bootstrap them
     *
     * @return void
     * 
     */
    private function initCore() {
        $this->app->get(Core::class)->init();
    }

    private function viewRegisterer(): ViewRegisterer {
        return $this->app->get(ViewRegisterer::class);
    }
}