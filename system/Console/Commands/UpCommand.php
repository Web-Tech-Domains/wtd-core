<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\MaintenanceMode;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Disables persistent maintenance mode.
 */
final class UpCommand implements Command
{
    public function __construct(private readonly MaintenanceMode $maintenanceMode)
    {
    }

    public function name(): string
    {
        return 'up';
    }

    public function description(): string
    {
        return 'Disable maintenance mode.';
    }

    public function handle(Input $input, Output $output): int
    {
        $this->maintenanceMode->disable();
        $output->line('Maintenance mode disabled');

        return 0;
    }
}
