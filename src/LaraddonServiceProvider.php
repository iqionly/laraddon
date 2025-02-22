<?php

namespace Iqionly\Laraddon;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class LaraddonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Core::class, function ($app) {
            return new Core($app);
        });
        
    }
    
    public function boot(): void
    {
        $this->loadKernels();
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

    /**
     * Load All Kernels and bootstrap them
     *
     * @return void
     * 
     */
    private function loadKernels() {
        $this->app->get(Core::class)->init()->registerRoutes();

        // dd($this->app->get('router')->getRoutes());
    }
}