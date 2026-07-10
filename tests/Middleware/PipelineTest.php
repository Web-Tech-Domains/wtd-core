<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Closure;
use PHPUnit\Framework\TestCase;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;
use WTD\Middleware\Pipeline;

final class PipelineTest extends TestCase
{
    public function testPipelineRunsMiddlewareAroundDestination(): void
    {
        $pipeline = new Pipeline();
        $middleware = new HeaderMiddleware();

        $response = $pipeline->handle(
            new Request('GET', '/'),
            [$middleware],
            static fn (): Response => Response::make('OK'),
        );

        self::assertSame('OK', $response->content());
        self::assertTrue($middleware->handled);
    }
}

final class HeaderMiddleware implements Middleware
{
    public bool $handled = false;

    public function handle(Request $request, Closure $next): Response
    {
        $this->handled = true;

        return $next($request);
    }
}
