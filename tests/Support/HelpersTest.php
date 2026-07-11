<?php

declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\View\ViewRenderer;

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

    public function testViewHelpersRenderNormalAndModuleViews(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $files = new Filesystem();
        $files->put($basePath . '/tests/tmp/helper-views/home.php', 'Hello {{ name }}');
        $files->put($basePath . '/modules/HelperModule/Resources/views/pages/index.php', 'Module {{ name }}');
        $container = new Container();
        $app = new Application($basePath, $container, new Repository([
            'view.path' => 'tests/tmp/helper-views',
            'view.extension' => '.php',
        ]));
        $container->singleton(Filesystem::class, static fn (): Filesystem => $files);
        $container->singleton(ViewRenderer::class);
        $GLOBALS['wtd_app'] = $app;

        try {
            self::assertSame('Hello WTD', view('home', ['name' => 'WTD']));
            self::assertSame('Module Blog', module_view('HelperModule', 'pages.index', ['name' => 'Blog']));
        } finally {
            unset($GLOBALS['wtd_app']);
            @unlink($basePath . '/tests/tmp/helper-views/home.php');
            @rmdir($basePath . '/tests/tmp/helper-views');
            @unlink($basePath . '/modules/HelperModule/Resources/views/pages/index.php');
            @rmdir($basePath . '/modules/HelperModule/Resources/views/pages');
            @rmdir($basePath . '/modules/HelperModule/Resources/views');
            @rmdir($basePath . '/modules/HelperModule/Resources');
            @rmdir($basePath . '/modules/HelperModule');
        }
    }
}
