<?php

declare(strict_types=1);

namespace Tests\Routing;

use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;
use WTD\Middleware\MiddlewareResolver;
use WTD\Middleware\Pipeline;
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
        $router->domain('cache.example.test', function (Router $router): void {
            $router->get('/cached/{id}', [CachedController::class, 'show'])
                ->name('cached.show')
                ->middleware(CachedRouteMiddleware::class);
        });

        $cache->write($router);

        $loaded = $this->router();
        $cache->load($loaded);

        self::assertSame('Cached 7', $loaded->dispatch(new Request('GET', '/cached/7', ['host' => 'cache.example.test']))->content());
        self::assertSame('cached', $loaded->dispatch(new Request('GET', '/cached/8', ['host' => 'cache.example.test']))->headers()['X-Cached-Route-Middleware']);
        $route = $loaded->route('cached.show');

        self::assertNotNull($route);
        self::assertSame('/cached/{id}', $route->path());
        self::assertSame('cache.example.test', $route->getDomain());
        self::assertSame([CachedRouteMiddleware::class], $route->getMiddleware());
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

        return new Router(
            new ControllerDispatcher($container),
            new MiddlewareResolver($container),
            new Pipeline(),
        );
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

final class CachedRouteMiddleware implements Middleware
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request)->withHeader('X-Cached-Route-Middleware', 'cached');
    }
}
