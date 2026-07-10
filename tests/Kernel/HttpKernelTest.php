<?php

declare(strict_types=1);

namespace Tests\Kernel;

use Closure;
use PHPUnit\Framework\TestCase;
use WTD\Container\Container;
use WTD\Config\Repository;
use WTD\Exception\ExceptionRenderer;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Kernel\HttpKernel;
use WTD\Logging\Logger;
use WTD\Middleware\Middleware;
use WTD\Middleware\Pipeline;
use WTD\Routing\ControllerDispatcher;
use WTD\Routing\Router;

final class HttpKernelTest extends TestCase
{
    public function testHttpKernelDispatchesThroughRouter(): void
    {
        $container = new Container();
        $container->instance(Container::class, $container);
        $router = new Router(new ControllerDispatcher($container));
        $router->get('/', static fn (): Response => Response::make('Home'));

        $kernel = new HttpKernel($router, new Pipeline(), $this->renderer(), [new KernelMiddleware()]);

        self::assertSame('Home', $kernel->handle(new Request('GET', '/'))->content());
    }

    public function testHttpKernelRendersNotFoundResponses(): void
    {
        $container = new Container();
        $container->instance(Container::class, $container);
        $router = new Router(new ControllerDispatcher($container));
        $kernel = new HttpKernel($router, new Pipeline(), $this->renderer());

        $response = $kernel->handle(new Request('GET', '/missing'));

        self::assertSame(404, $response->status());
        self::assertSame('Not Found', $response->content());
    }

    public function testHttpKernelRendersMethodNotAllowedResponses(): void
    {
        $container = new Container();
        $container->instance(Container::class, $container);
        $router = new Router(new ControllerDispatcher($container));
        $router->get('/submit', static fn (): string => 'OK');
        $kernel = new HttpKernel($router, new Pipeline(), $this->renderer());

        $response = $kernel->handle(new Request('POST', '/submit'));

        self::assertSame(405, $response->status());
        self::assertSame('Method Not Allowed', $response->content());
        self::assertSame('GET, HEAD, OPTIONS', $response->headers()['Allow']);
    }

    public function testHttpKernelRendersServerErrors(): void
    {
        $container = new Container();
        $container->instance(Container::class, $container);
        $router = new Router(new ControllerDispatcher($container));
        $router->get('/fail', static fn (): never => throw new \RuntimeException('Failure'));
        $kernel = new HttpKernel($router, new Pipeline(), $this->renderer());

        $response = $kernel->handle(new Request('GET', '/fail'));

        self::assertSame(500, $response->status());
        self::assertSame('Server Error', $response->content());
    }

    private function renderer(): ExceptionRenderer
    {
        return new ExceptionRenderer(
            new Repository(['app.debug' => false]),
            new Logger(dirname(__DIR__) . '/tmp/logs/http.log'),
        );
    }
}

final class KernelMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
