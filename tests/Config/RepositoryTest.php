<?php

declare(strict_types=1);

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use WTD\Config\Repository;

final class RepositoryTest extends TestCase
{
    public function testValuesCanBeReadAndWritten(): void
    {
        $config = new Repository(['app.name' => 'WTD Core']);

        self::assertTrue($config->has('app.name'));
        self::assertSame('WTD Core', $config->get('app.name'));
        self::assertSame('fallback', $config->get('missing', 'fallback'));

        $config->set('app.env', 'testing');

        self::assertSame('testing', $config->get('app.env'));
    }
}
