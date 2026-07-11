<?php

declare(strict_types=1);

namespace Modules\Blog\Http\Middleware;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

final class BlogMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
