<?php

declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\View\ViewRenderer;

final class ViewTest extends TestCase
{
    protected function tearDown(): void
    {
        @unlink(dirname(__DIR__) . '/tmp/views/greeting.php');
        @unlink(dirname(__DIR__, 2) . '/modules/TestModule/Resources/views/pages/index.php');
        @rmdir(dirname(__DIR__, 2) . '/modules/TestModule/Resources/views/pages');
        @rmdir(dirname(__DIR__, 2) . '/modules/TestModule/Resources/views');
        @rmdir(dirname(__DIR__, 2) . '/modules/TestModule/Resources');
        @rmdir(dirname(__DIR__, 2) . '/modules/TestModule');
        @rmdir(dirname(__DIR__) . '/tmp/views');
        @rmdir(dirname(__DIR__) . '/tmp');
    }

    public function testViewRendererRendersEscapedTemplateData(): void
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $files = new Filesystem();
        $files->put($basePath . '/tests/tmp/views/greeting.php', 'Hello {{ user.name }}');
        $renderer = new ViewRenderer(
            new Application($basePath, new Container(), new Repository()),
            new Repository([
                'view.path' => 'tests/tmp/views',
                'view.extension' => '.php',
            ]),
            $files,
        );

        self::assertSame('Hello &lt;Taylor&gt;', $renderer->render('greeting', [
            'user' => ['name' => '<Taylor>'],
        ]));
    }

    public function testViewRendererRendersModuleViews(): void
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $files = new Filesystem();
        $files->put($basePath . '/modules/TestModule/Resources/views/pages/index.php', 'Module {{ name }}');
        $renderer = new ViewRenderer(
            new Application($basePath, new Container(), new Repository()),
            new Repository(),
            $files,
        );

        self::assertSame('Module &lt;Blog&gt;', $renderer->renderModule('TestModule', 'pages.index', [
            'name' => '<Blog>',
        ]));
    }
}
