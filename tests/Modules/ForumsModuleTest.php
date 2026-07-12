<?php

declare(strict_types=1);

namespace Tests\Modules;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\Http\HttpServiceProvider;
use WTD\Http\Request;
use WTD\Kernel\HttpKernel;
use WTD\Logging\Logger;
use WTD\Modules\ModuleServiceProvider;
use WTD\View\ViewServiceProvider;

final class ForumsModuleTest extends TestCase
{
    public function testForumsModuleIsDiscoveredAndRendersVueWorkspace(): void
    {
        $app = new Application(
            dirname(__DIR__, 2),
            new Container(),
            new Repository([
                'modules.auto_discover' => true,
                'modules.enabled' => [],
                'view.path' => 'resources/views',
                'view.extension' => '.php',
                'assets.manifest' => 'public/build/.vite/manifest.json',
                'assets.hot_file' => 'public/hot',
                'assets.dev_server' => 'http://127.0.0.1:5173',
                'http.middleware' => [],
                'app.debug' => false,
                'developer.error_pages' => true,
            ]),
        );

        $app->container()->singleton(Filesystem::class);
        $app->container()->singleton(Logger::class, fn (): Logger => new Logger($app->basePath('storage/logs/tests-forums.log')));
        $app->register(HttpServiceProvider::class);
        $app->register(ViewServiceProvider::class);
        $app->register(ModuleServiceProvider::class);

        $GLOBALS['wtd_app'] = $app;

        try {
            $app->boot();

            /** @var HttpKernel $kernel */
            $kernel = $app->container()->get(HttpKernel::class);
            $response = $kernel->handle(new Request('GET', '/forums'));

            self::assertSame(200, $response->status());
            self::assertStringContainsString('data-forums-app', $response->content());
            self::assertStringContainsString('resources/js/modules/forums.js', $response->content());
            self::assertStringContainsString('Community topics', $response->content());
            self::assertStringContainsString('forums-initial-state', $response->content());
        } finally {
            unset($GLOBALS['wtd_app']);
        }
    }
}
