<?php

namespace Iqionly\Laraddon\Tests;

use Illuminate\Contracts\Config\Repository; 
use Orchestra\Testbench\Concerns\WithWorkbench; 

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app) 
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], function (Repository $config) { 
            $config->set('database.default', 'testbench'); 
            $config->set('database.connections.testbench', [ 
                'driver'   => 'sqlite', 
                'database' => ':memory:', 
                'prefix'   => '', 
            ]); 
        });
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app) 
    {
        return [
            'Iqionly\Laraddon\LaraddonServiceProvider',
        ];
    }

    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<string, class-string<\Illuminate\Support\Facades\Facade>>
     */
    protected function getPackageAliases($app) 
    {
        return [
            // 'Acme' => 'Acme\Facade',
        ];
    }

    /**
     * Ignore package discovery from.
     *
     * @return array<int, string>
     */
    public function ignorePackageDiscoveriesFrom() 
    {
        return [];
    }
}