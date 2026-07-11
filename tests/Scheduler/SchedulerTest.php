<?php

declare(strict_types=1);

namespace Tests\Scheduler;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
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
use WTD\Scheduler\CronExpression;
use WTD\Scheduler\Mutex;
use WTD\Scheduler\Scheduler;
use WTD\Scheduler\SchedulerServiceProvider;

final class SchedulerTest extends TestCase
{
    public function testCronExpressionMatchesDueTimes(): void
    {
        $cron = new CronExpression('*/15 9-17 * * 1-5');

        self::assertTrue($cron->isDue(new DateTimeImmutable('2026-07-10 09:30:00', new DateTimeZone('UTC'))));
        self::assertFalse($cron->isDue(new DateTimeImmutable('2026-07-11 09:30:00', new DateTimeZone('UTC'))));
        self::assertFalse($cron->isDue(new DateTimeImmutable('2026-07-10 09:31:00', new DateTimeZone('UTC'))));
    }

    public function testSchedulerSupportsTimezoneBackgroundAndMaintenanceFiltering(): void
    {
        $scheduler = new Scheduler();
        $ran = [];
        $time = new DateTimeImmutable('2026-07-10 14:00:00', new DateTimeZone('UTC'));

        $scheduler->call('new-york-daily', static function () use (&$ran): void {
            $ran[] = 'new-york-daily';
        })->dailyAt('10:00')->timezone('America/New_York')->runInBackground()->evenInMaintenanceMode();
        $scheduler->call('maintenance-only', static function () use (&$ran): void {
            $ran[] = 'maintenance-only';
        })->everyMinute()->evenInMaintenanceMode();
        $scheduler->call('blocked-in-maintenance', static function () use (&$ran): void {
            $ran[] = 'blocked-in-maintenance';
        })->everyMinute();

        $result = $scheduler->runDue($time, maintenanceMode: true);

        self::assertSame(['new-york-daily (background)', 'maintenance-only'], $result);
        self::assertSame(['new-york-daily', 'maintenance-only'], $ran);
    }

    public function testSchedulerPreventsOverlappingTasks(): void
    {
        $mutex = new Mutex();
        $scheduler = new Scheduler($mutex);
        $ran = [];
        $mutex->acquire('locked');

        $scheduler->call('locked', static function () use (&$ran): void {
            $ran[] = 'locked';
        })->everyMinute()->withoutOverlapping();

        self::assertSame([], $scheduler->runDue(new DateTimeImmutable('2026-07-10 10:00:00')));
        self::assertSame([], $ran);
    }

    public function testSchedulerServiceProviderAndCommandAreRegistered(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository([
            'app.name' => 'Scheduler Test',
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]));
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);

        /** @var Scheduler $scheduler */
        $scheduler = $app->container()->get(Scheduler::class);
        $scheduler->call('due', static function (): void {
        })->everyMinute();

        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);
        [$output, $stdout] = $this->consoleOutput();

        self::assertArrayHasKey('schedule:run', $kernel->commands());
        self::assertSame(0, $kernel->handle(new Input(['schedule:run']), $output));

        rewind($stdout);
        self::assertStringContainsString('Ran: due', (string) stream_get_contents($stdout));
    }

    /**
     * @return array{0: Output, 1: resource, 2: resource}
     */
    private function consoleOutput(): array
    {
        $stdout = fopen('php://temp', 'r+');
        $stderr = fopen('php://temp', 'r+');

        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        return [new Output($stdout, $stderr), $stdout, $stderr];
    }
}
