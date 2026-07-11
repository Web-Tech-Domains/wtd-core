<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Config\Repository;
use WTD\Console\Command;
use WTD\Console\ConsoleServiceProvider;
use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;
use WTD\Console\UnknownCommandException;
use WTD\Container\Container;
use WTD\Database\DatabaseServiceProvider;
use WTD\Http\HttpServiceProvider;
use WTD\Scheduler\SchedulerServiceProvider;

final class KernelTest extends TestCase
{
    public function testKernelDispatchesRegisteredCommand(): void
    {
        $kernel = new Kernel();
        $kernel->register(new ExampleCommand());
        [$output, $stdout] = $this->consoleOutput();

        $status = $kernel->handle(new Input(['example']), $output);

        rewind($stdout);
        self::assertSame(0, $status);
        self::assertSame('example handled' . PHP_EOL, stream_get_contents($stdout));
    }

    public function testKernelThrowsForUnknownCommand(): void
    {
        $this->expectException(UnknownCommandException::class);

        [$output] = $this->consoleOutput();
        (new Kernel())->handle(new Input(['missing']), $output);
    }

    public function testConsoleProviderRegistersBuiltInCommands(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);

        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);

        self::assertArrayHasKey('about', $kernel->commands());
        self::assertArrayHasKey('app:new', $kernel->commands());
        self::assertArrayHasKey('cache:clear', $kernel->commands());
        self::assertArrayHasKey('config:cache', $kernel->commands());
        self::assertArrayHasKey('config:clear', $kernel->commands());
        self::assertArrayHasKey('deploy', $kernel->commands());
        self::assertArrayHasKey('diagnostics', $kernel->commands());
        self::assertArrayHasKey('help', $kernel->commands());
        self::assertArrayHasKey('list', $kernel->commands());
        self::assertArrayHasKey('make:command', $kernel->commands());
        self::assertArrayHasKey('make:controller', $kernel->commands());
        self::assertArrayHasKey('make:model', $kernel->commands());
        self::assertArrayHasKey('migrate', $kernel->commands());
        self::assertArrayHasKey('migrate:rollback', $kernel->commands());
        self::assertArrayHasKey('optimize', $kernel->commands());
        self::assertArrayHasKey('optimize:clear', $kernel->commands());
        self::assertArrayHasKey('queue:work', $kernel->commands());
        self::assertArrayHasKey('route:cache', $kernel->commands());
        self::assertArrayHasKey('route:clear', $kernel->commands());
        self::assertArrayHasKey('schedule:run', $kernel->commands());
        self::assertArrayHasKey('db:seed', $kernel->commands());
        self::assertArrayHasKey('test', $kernel->commands());
    }

    public function testCliHelpersGenerateFilesAndReportUtilityCommands(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);
        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);
        [$output, $stdout] = $this->consoleOutput();

        self::assertSame(0, $kernel->handle(new Input([
            'make:controller',
            'AdminController',
            '--path=tests/tmp/cli/AdminController.php',
        ]), $output));
        self::assertFileExists(dirname(__DIR__) . '/tmp/cli/AdminController.php');

        self::assertSame(0, $kernel->handle(new Input([
            'make:model',
            'Post',
            '--path=tests/tmp/cli/Post.php',
        ]), $output));
        self::assertFileExists(dirname(__DIR__) . '/tmp/cli/Post.php');

        self::assertSame(0, $kernel->handle(new Input([
            'make:command',
            'SyncCommand',
            '--command=app:sync',
            '--path=tests/tmp/cli/SyncCommand.php',
        ]), $output));
        self::assertFileExists(dirname(__DIR__) . '/tmp/cli/SyncCommand.php');

        self::assertSame(0, $kernel->handle(new Input([
            'app:new',
            'demo',
            '--path=tests/tmp/cli/demo',
        ]), $output));
        self::assertFileExists(dirname(__DIR__) . '/tmp/cli/demo/README.md');

        self::assertSame(0, $kernel->handle(new Input(['queue:work']), $output));
        self::assertSame(0, $kernel->handle(new Input(['cache:clear']), $output));
        self::assertSame(0, $kernel->handle(new Input(['test']), $output));
        self::assertSame(0, $kernel->handle(new Input(['deploy']), $output));

        rewind($stdout);
        $contents = (string) stream_get_contents($stdout);
        self::assertStringContainsString('No queued jobs available', $contents);
        self::assertStringContainsString('Application cache cleared', $contents);
        self::assertStringContainsString('composer test', $contents);
        self::assertStringContainsString('"deployable"', $contents);
    }

    public function testHelpCommandCanDescribeSpecificCommand(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);
        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);
        [$output, $stdout] = $this->consoleOutput();

        $status = $kernel->handle(new Input(['help', 'about']), $output);

        rewind($stdout);
        self::assertSame(0, $status);
        self::assertStringContainsString('Print framework name and version.', (string) stream_get_contents($stdout));
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

    private function application(): Application
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        return new Application(
            $basePath,
            new Container(),
            new Repository([
                'app.name' => 'Console Test',
                'database.default' => 'sqlite',
                'database.connections.sqlite.driver' => 'sqlite',
                'database.connections.sqlite.database' => ':memory:',
            ]),
        );
    }
}

final class ExampleCommand implements Command
{
    public function name(): string
    {
        return 'example';
    }

    public function description(): string
    {
        return 'Example command.';
    }

    public function handle(Input $input, Output $output): int
    {
        $output->line('example handled');

        return 0;
    }
}
