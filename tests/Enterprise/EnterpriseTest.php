<?php

declare(strict_types=1);

namespace Tests\Enterprise;

use PHPUnit\Framework\TestCase;
use WTD\AI\AIServiceProvider;
use WTD\AI\AiManager;
use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Config\Repository;
use WTD\Console\ConsoleServiceProvider;
use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;
use WTD\Container\Container;
use WTD\Database\DatabaseServiceProvider;
use WTD\Http\HttpServiceProvider;
use WTD\Http\Request;
use WTD\Kernel\HttpKernel;
use WTD\Monitoring\MetricsRegistry;
use WTD\Monitoring\MonitoringServiceProvider;
use WTD\Monitoring\SystemMonitor;
use WTD\Scheduler\SchedulerServiceProvider;
use WTD\Tenancy\TenancyServiceProvider;
use WTD\Tenancy\TenantManager;
use WTD\Tenancy\TenantResolver;

final class EnterpriseTest extends TestCase
{
    private bool $registeredHandlers = false;

    protected function tearDown(): void
    {
        if ($this->registeredHandlers) {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function testTenancyResolvesConfiguredTenantFromRequest(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(TenancyServiceProvider::class);

        /** @var TenantResolver $resolver */
        $resolver = $app->container()->get(TenantResolver::class);
        /** @var TenantManager $manager */
        $manager = $app->container()->get(TenantManager::class);

        $tenant = $resolver->resolve(new Request('GET', '/', headers: ['X-Tenant' => 'acme']));
        $manager->use($tenant);

        self::assertSame('acme', $manager->current()?->id);
        self::assertSame('Acme Inc.', $manager->current()->name);
    }

    public function testAiManagerExposesDeterministicNullProvider(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(AIServiceProvider::class);

        /** @var AiManager $ai */
        $ai = $app->container()->get(AiManager::class);

        self::assertSame(['null'], $ai->providers());
        self::assertSame('hello', $ai->provider()->complete('hello'));
    }

    public function testMonitoringReportsDiagnosticsAndAdminRoute(): void
    {
        $app = $this->application([
            'monitoring.admin_enabled' => true,
            'monitoring.admin_path' => '/admin/system',
        ]);
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(MonitoringServiceProvider::class);

        /** @var MetricsRegistry $metrics */
        $metrics = $app->container()->get(MetricsRegistry::class);
        $metrics->increment('requests');
        $this->registeredHandlers = true;
        $app->boot();

        /** @var SystemMonitor $monitor */
        $monitor = $app->container()->get(SystemMonitor::class);
        /** @var HttpKernel $kernel */
        $kernel = $app->container()->get(HttpKernel::class);

        self::assertSame(1, $monitor->report()['metrics']['requests']);
        self::assertStringContainsString('WTD Admin', $kernel->handle(new Request('GET', '/admin/system'))->content());
    }

    public function testEnterpriseCommandsAreRegistered(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);
        $app->register(TenancyServiceProvider::class);
        $app->register(AIServiceProvider::class);
        $app->register(MonitoringServiceProvider::class);
        $this->registeredHandlers = true;
        $app->boot();

        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);
        [$output, $stdout] = $this->consoleOutput();

        self::assertSame(0, $kernel->handle(new Input(['tenant:list']), $output));
        self::assertSame(0, $kernel->handle(new Input(['ai:providers']), $output));
        self::assertSame(0, $kernel->handle(new Input(['monitor:report']), $output));

        rewind($stdout);
        $contents = (string) stream_get_contents($stdout);
        self::assertStringContainsString('acme - Acme Inc.', $contents);
        self::assertStringContainsString('null', $contents);
        self::assertStringContainsString('"application"', $contents);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function application(array $config = []): Application
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        return new Application(
            $basePath,
            new Container(),
            new Repository(array_replace([
                'app.name' => 'Enterprise Test',
                'app.debug' => false,
                'http.middleware' => [],
                'tenancy.enabled' => true,
                'tenancy.header' => 'X-Tenant',
                'tenancy.default' => 'acme',
                'tenancy.tenants' => [
                    'acme' => ['name' => 'Acme Inc.'],
                ],
                'ai.default' => 'null',
                'monitoring.admin_enabled' => false,
                'database.default' => 'sqlite',
                'database.connections.sqlite.driver' => 'sqlite',
                'database.connections.sqlite.database' => ':memory:',
            ], $config)),
        );
    }

    /**
     * @return array{0: Output, 1: resource}
     */
    private function consoleOutput(): array
    {
        $stdout = fopen('php://temp', 'r+');
        $stderr = fopen('php://temp', 'r+');

        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        return [new Output($stdout, $stderr), $stdout];
    }
}
