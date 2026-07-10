<?php

declare(strict_types=1);

namespace Tests\Logging;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use WTD\Logging\Logger;

final class LoggerTest extends TestCase
{
    public function testLoggerWritesEntries(): void
    {
        $path = dirname(__DIR__) . '/tmp/logs/wtd.log';
        $logger = new Logger($path);

        self::assertInstanceOf(LoggerInterface::class, $logger);

        $logger->info('Framework started for {name}', ['name' => 'tests']);

        self::assertFileExists($path);
        self::assertStringContainsString('INFO: Framework started for tests', (string) file_get_contents($path));
    }
}
