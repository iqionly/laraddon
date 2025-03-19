<?php declare(strict_types=1);

namespace Iqionly\Laraddon\Tests\Unit;

use Iqionly\Laraddon\Core;
use PHPUnit\Framework\Attributes\CoversClass;

use Iqionly\Laraddon\Tests\TestCase;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(Core::class)]
final class CoreTest extends TestCase {
    protected Core $core;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->core = $this->app->make(Core::class);
        });

        parent::setUp();
    }

    public function testCoreCanInitiated(): void
    {
        $resolved = $this->app->resolved(Core::class);
        $this->assertTrue($resolved, 'Core is cannot resolved.');
    }

    #[Depends('testCoreCanInitiated')]
    public function testCoreIsWorkingProperly(): void
    {
        $this->assertTrue(method_exists($this->core, 'init'),'Core don\'t have method init.');
        $this->assertIsObject($this->core->init(), 'Core init method is not returning itself.');
    }
}