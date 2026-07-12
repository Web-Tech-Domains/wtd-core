<?php

declare(strict_types=1);

namespace WTD\Database;

use WTD\ORM\Model;
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
        $this->container()->singleton(
            Schema::class,
            fn (): Schema => new Schema($this->container()->get(Connection::class)),
        );
        $this->container()->singleton(
            MigrationRepository::class,
            fn (): MigrationRepository => new MigrationRepository($this->container()->get(Connection::class)),
        );
        $this->container()->singleton(
            MigrationRunner::class,
            function (): MigrationRunner {
                $paths = [$this->app->basePath('database/migrations')];
                $moduleMigrations = glob($this->app->basePath('modules/*/Database/Migrations'));
                if ($moduleMigrations !== false) {
                    foreach ($moduleMigrations as $modulePath) {
                        $paths[] = $modulePath;
                    }
                }
                return new MigrationRunner(
                    $this->container()->get(MigrationRepository::class),
                    $this->container()->get(Schema::class),
                    $paths,
                );
            }
        );
        $this->container()->singleton(
            SeederRunner::class,
            function (): SeederRunner {
                $paths = [$this->app->basePath('database/seeders')];
                $moduleSeeders = glob($this->app->basePath('modules/*/Database/Seeders'));
                if ($moduleSeeders !== false) {
                    foreach ($moduleSeeders as $modulePath) {
                        $paths[] = $modulePath;
                    }
                }
                return new SeederRunner(
                    $this->container()->get(Connection::class),
                    $paths,
                );
            }
        );
    }

    public function boot(): void
    {
        Model::setDatabaseManager($this->container()->get(DatabaseManager::class));
    }
}
