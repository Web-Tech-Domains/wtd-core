<?php

declare(strict_types=1);

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use WTD\Config\Cache;
use WTD\Filesystem\Filesystem;

final class CacheTest extends TestCase
{
    public function testCacheCanWriteLoadAndClearConfig(): void
    {
        $cache = new Cache(new Filesystem(), dirname(__DIR__) . '/tmp/framework/config.php');

        $cache->clear();
        self::assertFalse($cache->exists());

        $cache->write(['app.name' => 'Cached']);

        self::assertTrue($cache->exists());
        self::assertSame(['app.name' => 'Cached'], $cache->load());

        $cache->clear();
        self::assertFalse($cache->exists());
    }
}
