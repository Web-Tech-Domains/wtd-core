<?php

declare(strict_types=1);

namespace Tests\Middleware;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WTD\Container\Container;
use WTD\Middleware\Middleware;
use WTD\Middleware\MiddlewareResolver;

final class MiddlewareResolverTest extends TestCase
{
    public function testResolverBuildsMiddlewareInstances(): void
    {
        $resolver = new MiddlewareResolver(new Container());

        $middleware = $resolver->resolve([ResolvableMiddleware::class]);

        self::assertInstanceOf(ResolvableMiddleware::class, $middleware[0]);
    }

    public function testResolverRejectsInvalidMiddleware(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @var list<class-string<Middleware>> $middleware */
        $middleware = [InvalidMiddleware::class];

        (new MiddlewareResolver(new Container()))->resolve($middleware);
    }
}

final class ResolvableMiddleware implements Middleware
{
    public function handle(\WTD\Http\Request $request, \Closure $next): \WTD\Http\Response
    {
        return $next($request);
    }
}

final class InvalidMiddleware
{
}
