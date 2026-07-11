<?php

declare(strict_types=1);

namespace WTD\Security;

final class SecretsManager
{
    /**
     * @param array<string, string> $secrets
     */
    public function __construct(private array $secrets = [])
    {
    }

    public function set(string $key, string $value): void
    {
        $this->secrets[$key] = $value;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->secrets[$key] ?? $_ENV[$key] ?? $default;
    }

    public function mask(string $value): string
    {
        return strlen($value) <= 4 ? '****' : substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2);
    }
}
