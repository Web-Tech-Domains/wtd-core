<?php

declare(strict_types=1);

namespace WTD\Cache;

interface CacheStore
{
    public function get(string $key, mixed $default = null): mixed;

    public function put(string $key, mixed $value, ?int $ttlSeconds = null): void;

    public function forget(string $key): void;

    public function flush(): void;

    public function has(string $key): bool;
}
