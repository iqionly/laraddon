<?php declare(strict_types=1);

namespace Iqionly\Laraddon;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewFinderInterface;
use Iqionly\Laraddon\Registerer\ViewRegisterer;
use Iqionly\Laraddon\Debugs\Profiler;

class LaraddonServiceProvider extends ServiceProvider
{
    /**
     * @var array<int, string> $deferClasses
     */
    private array $deferClasses = [
        \Iqionly\Laraddon\Core::class,
        \Iqionly\Laraddon\Debugs\Profiler::class,
    ];

    /**
     * @var array<int, string> $classes
     */
    private array $classes = [
        \Iqionly\Laraddon\Registerer\RouteRegisterer::class,
        \Iqionly\Laraddon\Registerer\ViewRegisterer::class,
        \Iqionly\Laraddon\Registerer\ControllerRegisterer::class,
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

        $this->extendView(); // This need to change to a more dynamic way to register views from addons, like database saved paths
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
        foreach (array_merge($this->deferClasses, $this->classes) as $class) {
            $this->app->get($class)->init();
        }
    }
    
    /**
     * Register Necessary Classes
     *
     * @return void
     */
    private function registerClasses()
    {
        // Register the Core Class
        $this->app->singleton(Core::class, function ($app) {
            return new Core($app);
        });

        $this->app->singleton(Profiler::class, function (Container $app) {
            return new Profiler($app->get('router'), $app->get(Core::class));
        });

        // Register All Classes
        foreach ($this->classes as $class) {
            $this->app->singleton($class, function ($app) use ($class) {
                return new $class($app, $app->get(Core::class));
            });
        }
    }
    
    /**
     * Get ViewRegisterer instance
     *
     * @return ViewRegisterer
     */
    private function getViewRegisterer(): ViewRegisterer {
        return $this->app->get(ViewRegisterer::class);
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
    
    /**
     * Extend View Finder to register views from addons
     *
     * @return void
     */
    private function extendView(): void
    {
        $this->app->extend('view.finder', function (ViewFinderInterface $file_view_finder) {
            // This is temporary to register views of base addon
            // We need to auto add location base of addons folder listed
            // we don't put this in config file, read it from database and cached to php files

            $registerer = $this->getViewRegisterer();
            foreach ($registerer->listPathViewModules() as $key => $value) {
                $file_view_finder->addLocation($value);
            }
            return $file_view_finder;
        });
    }
}