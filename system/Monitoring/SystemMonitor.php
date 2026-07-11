<?php

declare(strict_types=1);

namespace WTD\Monitoring;

use WTD\Application\Application;
use WTD\Application\Diagnostics;

final class SystemMonitor
{
    public function __construct(
        private readonly Application $app,
        private readonly Diagnostics $diagnostics,
        private readonly MetricsRegistry $metrics,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function report(): array
    {
        return [
            'application' => $this->app->name(),
            'diagnostics' => $this->diagnostics->report(),
            'metrics' => $this->metrics->all(),
        ];
    }
}
