<?php

namespace Iqionly\Laraddon;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class LaraddonServiceProvider extends ServiceProvider
{
    private array $classes = [
        \Iqionly\Laraddon\RouteRegisterer::class
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
                return new $class($app);
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
}