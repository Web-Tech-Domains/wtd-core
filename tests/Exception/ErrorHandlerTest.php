<?php

declare(strict_types=1);

namespace Tests\Exception;

use ErrorException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Exception\ErrorHandler;
use WTD\Logging\Logger;

final class ErrorHandlerTest extends TestCase
{
    public function testErrorHandlerLogsThrowable(): void
    {
        $path = dirname(__DIR__) . '/tmp/logs/errors.log';
        $handler = new ErrorHandler(new Logger($path));

        $handler->handle(new RuntimeException('Example failure'));

        self::assertFileExists($path);
        self::assertStringContainsString('ERROR: Example failure', (string) file_get_contents($path));
    }

    public function testErrorHandlerConvertsPhpErrorsToExceptions(): void
    {
        $handler = new ErrorHandler(new Logger(dirname(__DIR__) . '/tmp/logs/php-errors.log'));
        $oldReporting = error_reporting(E_ALL);

        try {
            $this->expectException(ErrorException::class);
            $this->expectExceptionMessage('Example warning');

            $handler->handleError(E_USER_WARNING, 'Example warning', __FILE__, __LINE__);
        } finally {
            error_reporting($oldReporting);
        }
    }

    public function testShutdownHandlerIgnoresCleanShutdowns(): void
    {
        $handler = new ErrorHandler(new Logger(dirname(__DIR__) . '/tmp/logs/shutdown.log'));

        $handler->handleShutdown();

        self::assertFileDoesNotExist(dirname(__DIR__) . '/tmp/logs/shutdown.log');
    }
}
