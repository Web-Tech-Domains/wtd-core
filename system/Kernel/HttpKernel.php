<?php

declare(strict_types=1);

namespace WTD\Kernel;

use Throwable;
use WTD\Exception\ExceptionRenderer;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;
use WTD\Middleware\Pipeline;
use WTD\Routing\Router;

/**
 * Handles HTTP requests through middleware and routing.
 */
final class HttpKernel
{
    /**
     * @param list<Middleware> $middleware
     */
    public function __construct(
        private readonly Router $router,
        private readonly Pipeline $pipeline,
        private readonly ExceptionRenderer $exceptions,
        private readonly array $middleware = [],
    ) {
    }

    /**
     * Handle an HTTP request.
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->pipeline->handle(
                $request,
                $this->middleware,
                fn (Request $request): Response => $this->router->dispatch($request),
            );
        } catch (Throwable $throwable) {
            return $this->exceptions->render($throwable);
        }
    }
}
