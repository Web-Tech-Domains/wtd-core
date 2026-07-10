<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Application\Diagnostics;
use WTD\Application\HealthCheck;
use WTD\Application\MaintenanceMode;
use WTD\Application\MemoryMonitor;
use WTD\Application\PerformanceTimer;
use WTD\Application\ProviderBootstrapper;
use WTD\Application\Version;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
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
        self::assertSame([$provider], $app->providers());
    }

    public function testProviderBootstrapperRegistersConfiguredProviders(): void
    {
        $app = $this->application();

        (new ProviderBootstrapper($app))->bootstrap([ExampleProvider::class]);

        self::assertCount(1, $app->providers());
        self::assertInstanceOf(ExampleProvider::class, $app->providers()[0]);
    }

    public function testCoreProviderRegistersPsrContracts(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);

        self::assertSame($app->container(), $app->container()->get(ContainerInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $app->container()->get(LoggerInterface::class));
    }

    public function testMaintenanceModeCanBeToggled(): void
    {
        $maintenance = $this->maintenanceMode('down');

        self::assertFalse($maintenance->enabled());

        $maintenance->enable();
        self::assertTrue($maintenance->enabled());

        $maintenance->disable();
        self::assertFalse($maintenance->enabled());
    }

    public function testHealthCheckReportsApplicationState(): void
    {
        $app = $this->application();
        $maintenance = $this->maintenanceMode('health-down');
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

    public function testDiagnosticsReportsRuntimeState(): void
    {
        $app = $this->application();
        $maintenance = $this->maintenanceMode('diagnostics-down');
        $diagnostics = new Diagnostics($app, $maintenance, new MemoryMonitor(), new PerformanceTimer());

        self::assertSame('Lifecycle Test', $diagnostics->report()['application']);
        self::assertFalse($diagnostics->report()['maintenance']);
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

    private function maintenanceMode(string $name): MaintenanceMode
    {
        $maintenanceMode = new MaintenanceMode(new Filesystem(), dirname(__DIR__) . '/tmp/framework/' . $name);
        $maintenanceMode->disable();

        return $maintenanceMode;
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
