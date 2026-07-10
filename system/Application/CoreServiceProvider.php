<?php

declare(strict_types=1);

namespace WTD\Application;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
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
        $this->container()->singleton(
            MaintenanceMode::class,
            fn (): MaintenanceMode => new MaintenanceMode(
                $this->container()->get(Filesystem::class),
                $this->app->basePath('storage/framework/down'),
            ),
        );
        $this->container()->singleton(MemoryMonitor::class);
        $this->container()->singleton(PerformanceTimer::class);
        $this->container()->singleton(Version::class);
        $this->container()->singleton(Logger::class, fn (): Logger => new Logger($this->app->basePath('storage/logs/wtd.log')));
        $this->container()->singleton(LoggerInterface::class, fn (): LoggerInterface => $this->container()->get(Logger::class));
        $this->container()->instance(ContainerInterface::class, $this->container());
        $this->container()->singleton(ErrorHandler::class);
        $this->container()->singleton(HealthCheck::class);
        $this->container()->singleton(Diagnostics::class);
    }
}
