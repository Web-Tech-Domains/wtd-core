<?php

declare(strict_types=1);

namespace Tests\Exception;

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
}
