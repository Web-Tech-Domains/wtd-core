<?php

declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\View\AssetManager;

final class AssetManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        @unlink(dirname(__DIR__) . '/tmp/assets/public/build/.vite/manifest.json');
        @rmdir(dirname(__DIR__) . '/tmp/assets/public/build/.vite');
        @rmdir(dirname(__DIR__) . '/tmp/assets/public/build');
        @rmdir(dirname(__DIR__) . '/tmp/assets/public');
        @rmdir(dirname(__DIR__) . '/tmp/assets');
        @rmdir(dirname(__DIR__) . '/tmp');
    }

    public function testAssetManagerUsesViteManifestFiles(): void
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        (new Filesystem())->put($basePath . '/tests/tmp/assets/public/build/.vite/manifest.json', json_encode([
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'css' => ['assets/app.css'],
            ],
        ], JSON_THROW_ON_ERROR));

        $assets = new AssetManager(
            new Application($basePath, new Container(), new Repository()),
            new Repository([
                'assets.manifest' => 'tests/tmp/assets/public/build/.vite/manifest.json',
                'assets.hot_file' => 'tests/tmp/assets/public/hot',
            ]),
        );

        $tags = $assets->tags(['resources/js/app.js']);

        self::assertStringContainsString('/build/assets/app.css', $tags);
        self::assertStringContainsString('/build/assets/app.js', $tags);
    }

    public function testAssetManagerDoesNotExposeSourcePathsWithoutManifestOrHotServer(): void
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $assets = new AssetManager(
            new Application($basePath, new Container(), new Repository()),
            new Repository([
                'assets.manifest' => 'tests/tmp/assets/missing-manifest.json',
                'assets.hot_file' => 'tests/tmp/assets/missing-hot',
            ]),
        );

        self::assertSame('', $assets->url('resources/js/modules/forums.js'));
        self::assertSame('', $assets->tags(['resources/js/modules/forums.js']));
    }
}
