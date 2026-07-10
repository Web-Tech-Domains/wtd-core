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
        $app->register(ConsoleServiceProvider::class);

        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);

        self::assertArrayHasKey('about', $kernel->commands());
        self::assertArrayHasKey('config:cache', $kernel->commands());
        self::assertArrayHasKey('config:clear', $kernel->commands());
        self::assertArrayHasKey('diagnostics', $kernel->commands());
        self::assertArrayHasKey('help', $kernel->commands());
        self::assertArrayHasKey('list', $kernel->commands());
        self::assertArrayHasKey('optimize', $kernel->commands());
        self::assertArrayHasKey('optimize:clear', $kernel->commands());
    }

    public function testHelpCommandCanDescribeSpecificCommand(): void
    {
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
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
            new Repository(['app.name' => 'Console Test']),
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
