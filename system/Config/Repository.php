<?php

declare(strict_types=1);

namespace WTD\Config;

/**
 * Stores framework configuration values with dot-notation access.
 */
final class Repository
{
    /**
     * @param array<string, mixed> $items
     */
    public function __construct(private array $items = [])
    {
    }

    /**
     * Determine whether a configuration key exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get a configuration value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * Set a configuration value.
     */
    public function set(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * Replace all configuration values.
     *
     * @param array<string, mixed> $items
     */
    public function replace(array $items): void
    {
        $this->items = $items;
    }

    /**
     * Merge a nested configuration array under a namespace.
     *
     * @param array<string, mixed> $items
     */
    public function merge(string $namespace, array $items): void
    {
        foreach ($this->flatten($items, $namespace) as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Return all configuration values.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Flatten nested configuration values into dot notation.
     *
     * @param array<string, mixed> $items
     *
     * @return array<string, mixed>
     */
    private function flatten(array $items, string $prefix): array
    {
        $flattened = [];

        foreach ($items as $key => $value) {
            $path = $prefix . '.' . $key;

            if (is_array($value)) {
                $flattened += $this->flatten($value, $path);
                continue;
            }

            $flattened[$path] = $value;
        }

        return $flattened;
    }
}
