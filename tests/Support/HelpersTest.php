<?php

declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testPathHelpersReturnProjectPaths(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        self::assertSame(dirname(__DIR__, 2), base_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'app', app_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config', config_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public', public_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage', storage_path());
    }
}
