<?php

declare(strict_types=1);

namespace WTD\Middleware;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;

/**
 * Defines HTTP middleware.
 */
interface Middleware
{
    /**
     * Handle an HTTP request.
     *
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response;
}
