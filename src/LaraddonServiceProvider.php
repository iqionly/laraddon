<?php declare(strict_types=1);

namespace Laraddon;

use Error;
use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewFinderInterface;
use Laraddon\Registerer\ViewRegisterer;
use Laraddon\Debugs\Profiler;

class LaraddonServiceProvider extends ServiceProvider
{
    /**
     * @var array<int, string> $deferClasses
     */
    private array $deferClasses = [
        \Laraddon\Core::class,
        \Laraddon\Debugs\Profiler::class,
    ];

    /**
     * @var array<int, string> $classes
     */
    private array $classes = [
        \Laraddon\Registerer\ViewRegisterer::class,
        \Laraddon\Registerer\ControllerRegisterer::class,
        \Laraddon\Registerer\RouteRegisterer::class,
    ];

    /**
     * @var string|null $addons_path
     */
    protected $addons_path = null;

    public function register(): void
    {
        $this->configuration();
        $this->registerClasses();
    }
    
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../views', 'laraddon');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->initClasses();
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

    private function configuration(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laraddon.php', 'laraddon');
        $this->determineAddonsPath();
    }

    /**
     * Load All Kernels and bootstrap them
     *
     * @return void
     * 
     */
    private function initClasses() {
        $this->app->booted(function() {
            foreach (array_merge($this->deferClasses, $this->classes) as $class) {
                try{
                    $this->app->get($class)->init();
                } catch (Error $e) {
                    // if it fails to initialize, run in safemode or like laravel normal system
                    $this->app->get('log')->error("Error initializing class: {$class}", [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        });
    }
    
    /**
     * Register Necessary Classes
     *
     * @return void
     */
    private function registerClasses()
    {
        // Register the Core Class
        $this->app->instance(Core::class, new Core($this->app));

        $this->app->singleton(Profiler::class, function (Application $app) {
            return new Profiler($app->get('router'), $app->get(Core::class));
        });

        // Register All Classes
        foreach ($this->classes as $class) {
            $this->app->singleton($class, function ($app) use ($class) {
                return new $class($app, $app->get(Core::class));
            });
        }
    }

    private function determineAddonsPath(): void
    {
        if($this->app->runningUnitTests() && env('PHPUNIT_ADDONS_PATH') != null) {
            $path = realpath(__DIR__ . env('PHPUNIT_ADDONS_PATH'));
            if($path === false) {
                throw new \ErrorException('Invalid addons path provided for PHPUnit tests.');
            }
            $this->addons_path = $path;
            $this->app->get('config')->set('laraddon.addons_path', $path);
            unset($path);
        } else {
            $this->addons_path = $this->app->get('config')->get('laraddon.addons_path');
        }
    }
}