<?php

declare(strict_types=1);

namespace WTD\Cache;

final class Lock
{
    public function __construct(
        private readonly CacheStore $store,
        private readonly string $name,
        private readonly int $ttlSeconds,
    ) {
    }

    public function acquire(): bool
    {
        if ($this->store->has($this->key())) {
            return false;
        }

        $this->store->put($this->key(), true, $this->ttlSeconds);

        return true;
    }

    public function release(): void
    {
        $this->store->forget($this->key());
    }

    private function key(): string
    {
        return 'lock:' . $this->name;
    }
}
