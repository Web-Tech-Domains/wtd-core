<?php

declare(strict_types=1);

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use WTD\Config\Loader;
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

    public function testNestedValuesCanBeMergedWithNamespace(): void
    {
        $config = new Repository();
        $config->merge('app', ['services' => ['cache' => 'file']]);

        self::assertSame('file', $config->get('app.services.cache'));
    }

    public function testLoaderImportsConfigDirectory(): void
    {
        $path = dirname(__DIR__) . '/tmp/config';
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        file_put_contents($path . '/app.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => 'Loaded'];\n");

        $config = new Repository();
        (new Loader($config))->loadDirectory($path);

        self::assertSame('Loaded', $config->get('app.name'));
    }
}
