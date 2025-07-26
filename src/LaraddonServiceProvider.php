<?php declare(strict_types=1);

namespace Laraddon;

use Error;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\LogManager;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewFinderInterface;
use Laraddon\Registerer\ViewRegisterer;
use Laraddon\Debugs\Profiler;
use Laraddon\Interfaces\Initiable;

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
                    $class = $this->app->get($class);

                    if($class instanceof Initiable) {
                        // If the class implements Initiable, call init method
                        $class = $class->init();
                    } else {
                        throw new \ErrorException("There some class is not Initiable.", 11000);
                    }
                } catch (Error $e) {
                    // if it fails to initialize, run in safemode or like laravel normal system
                    ((object) $this->app->get(LogManager::class))->error("Error initializing class: {$class}", [
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
            return new Profiler($app->get(Router::class), $app->get(Core::class));
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
        $unit_addon_path = env('PHPUNIT_ADDONS_PATH');
        if($this->app->runningUnitTests() && is_string($unit_addon_path = env('PHPUNIT_ADDONS_PATH'))) {
            $path = realpath(__DIR__ . $unit_addon_path);
            if($path === false) {
                throw new \ErrorException('Invalid addons path provided for PHPUnit tests.', 11001);
            }
            $this->addons_path = $path;
            $this->app->get(Repository::class)->set('laraddon.addons_path', $path);
            unset($path);
        } else {
            $path = $this->app->get(Repository::class)->get('laraddon.addons_path');
            if(!is_string($path)) {
                throw new \ErrorException("Configuration 'laraddon.addons_path' must be a string.", 11002);
            }
            $this->addons_path = $path;
            unset($path);
        }
    }
}