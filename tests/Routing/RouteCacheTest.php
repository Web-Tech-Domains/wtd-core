<?php

declare(strict_types=1);

namespace Tests\Routing;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\Http\Request;
use WTD\Routing\ControllerDispatcher;
use WTD\Routing\RouteCache;
use WTD\Routing\Router;

final class RouteCacheTest extends TestCase
{
    public function testRouteCacheWritesAndLoadsControllerRoutes(): void
    {
        $path = dirname(__DIR__) . '/tmp/framework/routes.php';
        $cache = new RouteCache(new Filesystem(), $path);
        $cache->clear();

        $router = $this->router();
        $router->get('/cached/{id}', [CachedController::class, 'show'])->name('cached.show');

        $cache->write($router);

        $loaded = $this->router();
        $cache->load($loaded);

        self::assertSame('Cached 7', $loaded->dispatch(new Request('GET', '/cached/7'))->content());
        self::assertSame('/cached/{id}', $loaded->route('cached.show')?->path());
    }

    public function testRouteCacheRejectsClosureRoutes(): void
    {
        $this->expectException(RuntimeException::class);

        $router = $this->router();
        $router->get('/closure', static fn (): string => 'No');

        (new RouteCache(new Filesystem(), dirname(__DIR__) . '/tmp/framework/closure-routes.php'))->write($router);
    }

    private function router(): Router
    {
        $container = new Container();
        $container->instance(Container::class, $container);

        return new Router(new ControllerDispatcher($container));
    }
}

final class CachedController
{
    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters): string
    {
        return 'Cached ' . $parameters['id'];
    }
}
