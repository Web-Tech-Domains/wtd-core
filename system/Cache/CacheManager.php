<?php

declare(strict_types=1);

namespace WTD\Cache;

use InvalidArgumentException;

final class CacheManager
{
    /**
     * @var array<string, CacheRepository>
     */
    private array $stores = [];

    public function __construct(private readonly string $default = 'file')
    {
        $this->stores = [
            'file' => new CacheRepository(new FileStore()),
            'redis' => new CacheRepository(new RedisStore()),
            'memcached' => new CacheRepository(new MemcachedStore()),
        ];
    }

    public function store(?string $name = null): CacheRepository
    {
        $name ??= $this->default;

        return $this->stores[$name] ?? throw new InvalidArgumentException(sprintf('Cache store [%s] is not configured.', $name));
    }
}
