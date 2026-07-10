<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\HealthCheck;
use WTD\Application\MemoryMonitor;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Prints a JSON health report.
 */
final class HealthCommand implements Command
{
    public function __construct(
        private readonly HealthCheck $healthCheck,
        private readonly MemoryMonitor $memoryMonitor,
    ) {
    }

    public function name(): string
    {
        return 'health';
    }

    public function description(): string
    {
        return 'Print application health as JSON.';
    }

    public function handle(Input $input, Output $output): int
    {
        $output->json([
            ...$this->healthCheck->report(),
            'memory' => [
                'usage' => $this->memoryMonitor->usage(),
                'peak' => $this->memoryMonitor->peak(),
            ],
        ]);

        return 0;
    }
}
