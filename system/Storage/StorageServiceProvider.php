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
                (string) $this->app->config()->get('filesystems.disks.local.url', '/storage'),
                (string) $this->app->config()->get('app.key', 'wtd-core'),
                [
                    's3' => (string) $this->app->config()->get('filesystems.disks.s3.url', 'https://s3.example.test'),
                    'r2' => (string) $this->app->config()->get('filesystems.disks.r2.url', 'https://r2.example.test'),
                    'azure' => (string) $this->app->config()->get('filesystems.disks.azure.url', 'https://azure.example.test'),
                    'gcs' => (string) $this->app->config()->get('filesystems.disks.gcs.url', 'https://gcs.example.test'),
                    'ftp' => (string) $this->app->config()->get('filesystems.disks.ftp.url', 'ftp://files.example.test'),
                    'sftp' => (string) $this->app->config()->get('filesystems.disks.sftp.url', 'sftp://files.example.test'),
                ],
            ),
        );
        $this->container()->singleton(StorageDisk::class, fn (): StorageDisk => $this->container()->get(StorageManager::class)->disk());
    }
}
