<?php

declare(strict_types=1);

namespace WTD\Tenancy;

use WTD\Config\Repository;

/**
 * Stores and resolves configured tenants for the current runtime.
 */
final class TenantManager
{
    private ?Tenant $current = null;

    public function __construct(private readonly Repository $config)
    {
    }

    /**
     * @return list<Tenant>
     */
    public function all(): array
    {
        $tenants = $this->config->get('tenancy.tenants', []);

        if (!is_array($tenants)) {
            return [];
        }

        $resolved = [];

        foreach ($tenants as $id => $tenant) {
            if (!is_array($tenant)) {
                continue;
            }

            $name = $tenant['name'] ?? $id;
            $resolved[] = new Tenant((string) $id, is_scalar($name) ? (string) $name : (string) $id, $tenant);
        }

        return $resolved;
    }

    public function find(string $id): ?Tenant
    {
        foreach ($this->all() as $tenant) {
            if ($tenant->id === $id) {
                return $tenant;
            }
        }

        return null;
    }

    public function current(): ?Tenant
    {
        return $this->current;
    }

    public function use(?Tenant $tenant): void
    {
        $this->current = $tenant;
    }
}
