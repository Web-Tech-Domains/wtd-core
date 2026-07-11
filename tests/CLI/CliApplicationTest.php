<?php

declare(strict_types=1);

namespace Tests\CLI;

use PHPUnit\Framework\TestCase;
use WTD\CLI\CliApplication;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;

final class CliApplicationTest extends TestCase
{
    public function testCliApplicationRunsConsoleKernel(): void
    {
        $kernel = new Kernel();
        $kernel->register(new CliTestCommand());
        $stdout = fopen('php://temp', 'r+');
        $stderr = fopen('php://temp', 'r+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        $status = (new CliApplication($kernel))->run(['core', 'cli:test'], new Output($stdout, $stderr));

        rewind($stdout);
        self::assertSame(0, $status);
        self::assertSame('cli ok' . PHP_EOL, stream_get_contents($stdout));
    }

    public function testCliApplicationReturnsFailureForUnknownCommand(): void
    {
        $stdout = fopen('php://temp', 'r+');
        $stderr = fopen('php://temp', 'r+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        $status = (new CliApplication(new Kernel()))->run(['core', 'missing'], new Output($stdout, $stderr));

        rewind($stderr);
        self::assertSame(1, $status);
        self::assertStringContainsString('Unknown command: missing', (string) stream_get_contents($stderr));
    }
}

final class CliTestCommand implements Command
{
    public function name(): string
    {
        return 'cli:test';
    }

    public function description(): string
    {
        return 'CLI test command.';
    }

    public function handle(Input $input, Output $output): int
    {
        $output->line('cli ok');

        return 0;
    }
}
