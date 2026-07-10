<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Application\HealthCheck;
use WTD\Application\MaintenanceMode;
use WTD\Application\MemoryMonitor;
use WTD\Application\PerformanceTimer;
use WTD\Application\Version;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Support\ServiceProvider;

final class LifecycleTest extends TestCase
{
    public function testServiceProviderRegistrationAndBooting(): void
    {
        $app = $this->application();
        $provider = new ExampleProvider($app);
        $app->register($provider);
        $app->boot();

        self::assertTrue($provider->registered);
        self::assertTrue($provider->booted);
        self::assertTrue($app->isBooted());
    }

    public function testMaintenanceModeCanBeToggled(): void
    {
        $maintenance = new MaintenanceMode();

        self::assertFalse($maintenance->enabled());

        $maintenance->enable();
        self::assertTrue($maintenance->enabled());

        $maintenance->disable();
        self::assertFalse($maintenance->enabled());
    }

    public function testHealthCheckReportsApplicationState(): void
    {
        $app = $this->application();
        $maintenance = new MaintenanceMode();
        $health = new HealthCheck($app, $maintenance);

        self::assertSame('ok', $health->report()['status']);

        $maintenance->enable();
        self::assertSame('maintenance', $health->report()['status']);
    }

    public function testPerformanceTimerAndMemoryMonitorExposeMetrics(): void
    {
        $timer = new PerformanceTimer();
        $memory = new MemoryMonitor();

        self::assertGreaterThanOrEqual(0.0, $timer->elapsedMilliseconds());
        self::assertGreaterThan(0, $memory->usage());
        self::assertGreaterThan(0, $memory->peak());
    }

    public function testVersionReportsApplicationVersion(): void
    {
        self::assertSame(Application::VERSION, (new Version())->current());
    }

    private function application(): Application
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        return new Application(
            $basePath,
            new Container(),
            new Repository(['app.name' => 'Lifecycle Test']),
        );
    }
}

final class ExampleProvider extends ServiceProvider
{
    public bool $registered = false;

    public bool $booted = false;

    public function register(): void
    {
        $this->registered = true;
    }

    public function boot(): void
    {
        $this->booted = true;
    }
}
