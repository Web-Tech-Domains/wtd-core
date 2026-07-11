<?php

declare(strict_types=1);

namespace WTD\Tenancy;

/**
 * Represents an enterprise tenant.
 */
final class Tenant
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $metadata = [],
    ) {
    }
}
