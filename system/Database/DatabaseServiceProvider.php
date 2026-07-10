<?php

declare(strict_types=1);

namespace WTD\Database;

use WTD\Support\ServiceProvider;

/**
 * Registers database services.
 */
final class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database service bindings.
     */
    public function register(): void
    {
        $this->container()->singleton(
            DatabaseManager::class,
            fn (): DatabaseManager => new DatabaseManager($this->app->config()),
        );
        $this->container()->singleton(
            Connection::class,
            fn (): Connection => $this->container()->get(DatabaseManager::class)->connection(),
        );
    }
}
