<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\MaintenanceMode;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Enables persistent maintenance mode.
 */
final class DownCommand implements Command
{
    public function __construct(private readonly MaintenanceMode $maintenanceMode)
    {
    }

    public function name(): string
    {
        return 'down';
    }

    public function description(): string
    {
        return 'Enable maintenance mode.';
    }

    public function handle(Input $input, Output $output): int
    {
        $this->maintenanceMode->enable();
        $output->line('Maintenance mode enabled');

        return 0;
    }
}
