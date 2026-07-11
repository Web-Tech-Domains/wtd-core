<?php

declare(strict_types=1);

namespace WTD\Cache;

class ArrayStore implements CacheStore
{
    /**
     * @var array<string, array{value: mixed, expires: int|null}>
     */
    private array $items = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->items[$key] ?? null;

        if ($item === null) {
            return $default;
        }

        if ($item['expires'] !== null && $item['expires'] <= time()) {
            unset($this->items[$key]);

            return $default;
        }

        return $item['value'];
    }

    public function put(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $this->items[$key] = [
            'value' => $value,
            'expires' => $ttlSeconds === null ? null : time() + $ttlSeconds,
        ];
    }

    public function forget(string $key): void
    {
        unset($this->items[$key]);
    }

    public function flush(): void
    {
        $this->items = [];
    }

    public function has(string $key): bool
    {
        return $this->get($key, new MissingValue()) instanceof MissingValue === false;
    }
}
