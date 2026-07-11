<?php

declare(strict_types=1);

namespace WTD\Queue;

use WTD\Support\ServiceProvider;

final class QueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(
            QueueManager::class,
            fn (): QueueManager => new QueueManager((string) $this->app->config()->get('queue.default', 'database')),
        );
        $this->container()->singleton(QueueDriver::class, fn (): QueueDriver => $this->container()->get(QueueManager::class)->connection());
        $this->container()->singleton(FailedJobProvider::class, fn (): FailedJobProvider => new FailedJobProvider());
        $this->container()->singleton(BatchRepository::class, fn (): BatchRepository => new BatchRepository());
        $this->container()->singleton(
            Worker::class,
            fn (): Worker => new Worker(
                $this->container()->get(QueueDriver::class),
                $this->container()->get(FailedJobProvider::class),
                $this->container()->get(BatchRepository::class),
            ),
        );
    }
}
