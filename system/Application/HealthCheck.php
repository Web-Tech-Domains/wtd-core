<?php

declare(strict_types=1);

namespace WTD\Application;

/**
 * Produces a minimal health payload for diagnostics.
 */
final class HealthCheck
{
    public function __construct(
        private readonly Application $app,
        private readonly MaintenanceMode $maintenanceMode,
    ) {
    }

    /**
     * Return the current application health state.
     *
     * @return array{status: string, application: string, version: string, maintenance: bool}
     */
    public function report(): array
    {
        return [
            'status' => $this->maintenanceMode->enabled() ? 'maintenance' : 'ok',
            'application' => $this->app->name(),
            'version' => $this->app->version(),
            'maintenance' => $this->maintenanceMode->enabled(),
        ];
    }
}
