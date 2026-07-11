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

    public function testListValuesCanBeMergedWithNamespace(): void
    {
        $config = new Repository();
        $middleware = [
            'Tests\\Middleware\\FirstMiddleware',
            'Tests\\Middleware\\SecondMiddleware',
        ];

        $config->merge('http', ['middleware' => $middleware]);

        self::assertSame($middleware, $config->get('http.middleware'));
        self::assertSame('fallback', $config->get('http.middleware.0', 'fallback'));
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

    public function testDefaultEnvironmentIsDevelopmentWhenEnvFileIsMissing(): void
    {
        $oldAppEnv = $_ENV['APP_ENV'] ?? null;
        $oldServerEnv = $_SERVER['APP_ENV'] ?? null;
        $oldAppDebug = $_ENV['APP_DEBUG'] ?? null;
        $oldServerDebug = $_SERVER['APP_DEBUG'] ?? null;
        $oldProcessEnv = getenv('APP_ENV');
        $oldProcessDebug = getenv('APP_DEBUG');

        unset($_ENV['APP_ENV'], $_SERVER['APP_ENV'], $_ENV['APP_DEBUG'], $_SERVER['APP_DEBUG']);
        putenv('APP_ENV');
        putenv('APP_DEBUG');

        try {
            /** @var array<string, mixed> $app */
            $app = require dirname(__DIR__, 2) . '/config/app.php';
            /** @var array<string, mixed> $developer */
            $developer = require dirname(__DIR__, 2) . '/config/developer.php';

            self::assertSame('development', $app['env']);
            self::assertTrue($app['debug']);
            self::assertTrue($developer['enabled']);
        } finally {
            $this->restoreEnv('APP_ENV', $oldAppEnv, $oldServerEnv, $oldProcessEnv);
            $this->restoreEnv('APP_DEBUG', $oldAppDebug, $oldServerDebug, $oldProcessDebug);
        }
    }

    private function restoreEnv(string $key, ?string $envValue, ?string $serverValue, string|false $processValue): void
    {
        if ($envValue === null) {
            unset($_ENV[$key]);
        } else {
            $_ENV[$key] = $envValue;
        }

        if ($serverValue === null) {
            unset($_SERVER[$key]);
        } else {
            $_SERVER[$key] = $serverValue;
        }

        if ($processValue === false) {
            putenv($key);
            return;
        }

        putenv($key . '=' . $processValue);
    }
}
