<?php

declare(strict_types=1);

namespace WTD\Storage;

use WTD\Support\ServiceProvider;

final class StorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(
            StorageManager::class,
            fn (): StorageManager => new StorageManager(
                (string) $this->app->config()->get('filesystems.default', 'local'),
                $this->app->basePath((string) $this->app->config()->get('filesystems.disks.local.root', 'storage/app')),
                (string) $this->app->config()->get('app.key', 'wtd-core'),
            ),
        );
        $this->container()->singleton(StorageDisk::class, fn (): StorageDisk => $this->container()->get(StorageManager::class)->disk());
    }
}
