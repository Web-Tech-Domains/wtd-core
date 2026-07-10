<?php

declare(strict_types=1);

namespace WTD\Application;

use WTD\Exception\ErrorHandler;
use WTD\Filesystem\Filesystem;
use WTD\Logging\Logger;
use WTD\Support\ServiceProvider;

/**
 * Registers core framework services.
 */
final class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register core service bindings.
     */
    public function register(): void
    {
        $this->container()->singleton(Filesystem::class);
        $this->container()->singleton(MaintenanceMode::class);
        $this->container()->singleton(MemoryMonitor::class);
        $this->container()->singleton(PerformanceTimer::class);
        $this->container()->singleton(Version::class);
        $this->container()->singleton(Logger::class, fn (): Logger => new Logger($this->app->basePath('storage/logs/wtd.log')));
        $this->container()->singleton(ErrorHandler::class);
        $this->container()->singleton(HealthCheck::class);
    }
}
