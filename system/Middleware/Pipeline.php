<?php

declare(strict_types=1);

namespace WTD\Middleware;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;

/**
 * Executes HTTP middleware around a final handler.
 */
final class Pipeline
{
    /**
     * @param list<Middleware> $middleware
     * @param Closure(Request): Response $destination
     */
    public function handle(Request $request, array $middleware, Closure $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($middleware),
            /**
             * @param Closure(Request): Response $next
             *
             * @return Closure(Request): Response
             */
            static fn (Closure $next, Middleware $middleware): Closure => static fn (Request $request): Response => $middleware->handle($request, $next),
            $destination,
        );

        return $pipeline($request);
    }
}
