<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;

final class ApplicationTest extends TestCase
{
    public function testApplicationExposesCoreState(): void
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application(
            $basePath,
            new Container(),
            new Repository(['app.name' => 'Example']),
        );

        self::assertSame('Example', $app->name());
        self::assertSame(Application::VERSION, $app->version());
        self::assertTrue($app->container()->has(Application::class));
        self::assertSame('Example', $app->config()->get('app.name'));
    }
}
