<?php

declare(strict_types=1);

namespace WTD\Tenancy;

use Closure;
use WTD\Config\Repository;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

/**
 * Attaches the resolved tenant to the current runtime.
 */
final class TenantMiddleware implements Middleware
{
    public function __construct(
        private readonly Repository $config,
        private readonly TenantResolver $resolver,
        private readonly TenantManager $tenants,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ((bool) $this->config->get('tenancy.enabled', false)) {
            $this->tenants->use($this->resolver->resolve($request));
        }

        return $next($request);
    }
}
