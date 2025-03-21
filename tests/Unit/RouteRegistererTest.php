<?php declare(strict_types=1);

namespace Iqionly\Laraddon\Tests\Unit;

use Iqionly\Laraddon\RouteRegisterer;
use PHPUnit\Framework\Attributes\CoversClass;

use Iqionly\Laraddon\Tests\TestCase;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(RouteRegisterer::class)]
final class RouteRegistererTest extends TestCase {
    protected RouteRegisterer $route_registerer;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->route_registerer = $this->app->make(RouteRegisterer::class);
        });

        parent::setUp();
    }

    public function testRouteRegistererCanInitiated(): void
    {
        $resolved = $this->app->resolved(RouteRegisterer::class);
        $this->assertTrue($resolved, 'RouteRegisterer is cannot resolved.');
    }

    #[Depends('testRouteRegistererCanInitiated')]
    public function testRouteRegistererIsWorkingProperly(): void
    {
        $this->assertTrue(method_exists($this->route_registerer, 'init'),'RouteRegisterer don\'t have method init.');
        $this->assertIsObject($this->route_registerer->init(), 'RouteRegisterer init method is not returning itself.');
    }

    #[Depends('testRouteRegistererIsWorkingProperly')]
    public function testRouteTestGenerated(): void
    {
        dd($this->app->get('router')->getRoutes());
    }
}