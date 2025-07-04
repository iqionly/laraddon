<?php

namespace Iqionly\Laraddon;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewFinderInterface;
use Iqionly\Laraddon\Registerer\ViewRegisterer;

class LaraddonServiceProvider extends ServiceProvider
{
    private array $classes = [
        \Iqionly\Laraddon\Registerer\RouteRegisterer::class,
        \Iqionly\Laraddon\Registerer\ViewRegisterer::class,
        \Iqionly\Laraddon\Registerer\ControllerRegisterer::class,
    ];

    protected $addons_path = null;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laraddon.php', 'laraddon');

        $this->determineAddonsPath();

        $this->app->singleton(Core::class, function ($app) {
            return new Core($app);
        });

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
            return $file_view_finder;
        });
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

    private function determineAddonsPath(): void
    {
        if($this->app->runningUnitTests() && env('PHPUNIT_ADDONS_PATH') != null) {
            $this->addons_path = realpath(__DIR__ . env('PHPUNIT_ADDONS_PATH'));
            $this->app->get('config')->set('laraddon.addons_path', $this->addons_path);
        } else {
            $this->addons_path = $this->app->get('config')->get('laraddon.addons_path');
        }
    }
}