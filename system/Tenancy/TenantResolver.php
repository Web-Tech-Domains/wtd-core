<?php

declare(strict_types=1);

namespace WTD\Tenancy;

use WTD\Config\Repository;
use WTD\Http\Request;

/**
 * Resolves tenants from request headers or configured defaults.
 */
final class TenantResolver
{
    public function __construct(
        private readonly Repository $config,
        private readonly TenantManager $tenants,
    ) {
    }

    public function resolve(Request $request): ?Tenant
    {
        $header = $this->config->get('tenancy.header', 'X-Tenant');
        $header = is_scalar($header) ? (string) $header : 'X-Tenant';
        $tenantId = $request->header($header);

        if ($tenantId === null) {
            $default = $this->config->get('tenancy.default');
            $tenantId = is_scalar($default) ? (string) $default : null;
        }

        return $tenantId === null ? null : $this->tenants->find($tenantId);
    }
}
