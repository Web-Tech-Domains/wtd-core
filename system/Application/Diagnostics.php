<?php

declare(strict_types=1);

namespace WTD\Application;

/**
 * Builds a runtime diagnostics payload for CLI and health tooling.
 */
final class Diagnostics
{
    public function __construct(
        private readonly Application $app,
        private readonly MaintenanceMode $maintenanceMode,
        private readonly MemoryMonitor $memoryMonitor,
        private readonly PerformanceTimer $performanceTimer,
    ) {
    }

    /**
     * Return a diagnostic report for the current process.
     *
     * @return array<string, mixed>
     */
    public function report(): array
    {
        return [
            'application' => $this->app->name(),
            'version' => $this->app->version(),
            'environment' => $this->app->config()->get('app.env', 'production'),
            'debug' => $this->app->config()->get('app.debug', false),
            'base_path' => $this->app->basePath(),
            'booted' => $this->app->isBooted(),
            'maintenance' => $this->maintenanceMode->enabled(),
            'memory' => [
                'usage' => $this->memoryMonitor->usage(),
                'peak' => $this->memoryMonitor->peak(),
            ],
            'elapsed_ms' => $this->performanceTimer->elapsedMilliseconds(),
        ];
    }
}
