<?php

declare(strict_types=1);

namespace WTD\Security;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

/**
 * Applies secure response headers to every HTTP response.
 */
final class SecurityHeadersMiddleware implements Middleware
{
    public function __construct(private readonly SecurityHeaders $headers)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $this->headers->apply($next($request));
    }
}
