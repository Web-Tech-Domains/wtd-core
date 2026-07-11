<?php

declare(strict_types=1);

namespace WTD\Cache;

use WTD\Support\ServiceProvider;

final class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(
            CacheManager::class,
            fn (): CacheManager => new CacheManager((string) $this->app->config()->get('cache.default', 'file')),
        );
        $this->container()->singleton(CacheRepository::class, fn (): CacheRepository => $this->container()->get(CacheManager::class)->store());
    }
}
