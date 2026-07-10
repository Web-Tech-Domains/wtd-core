<?php

declare(strict_types=1);

namespace Tests\Logging;

use PHPUnit\Framework\TestCase;
use WTD\Logging\Logger;

final class LoggerTest extends TestCase
{
    public function testLoggerWritesEntries(): void
    {
        $path = dirname(__DIR__) . '/tmp/logs/wtd.log';
        $logger = new Logger($path);

        $logger->info('Framework started', ['test' => true]);

        self::assertFileExists($path);
        self::assertStringContainsString('INFO: Framework started', (string) file_get_contents($path));
    }
}
