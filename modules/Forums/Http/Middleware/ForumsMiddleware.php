<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Middleware;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

final class ForumsMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
