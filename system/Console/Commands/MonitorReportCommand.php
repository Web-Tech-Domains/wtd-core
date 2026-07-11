<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Monitoring\SystemMonitor;

final class MonitorReportCommand implements Command
{
    public function __construct(private readonly SystemMonitor $monitor)
    {
    }

    public function name(): string
    {
        return 'monitor:report';
    }

    public function description(): string
    {
        return 'Print enterprise monitoring and administration report.';
    }

    public function handle(Input $input, Output $output): int
    {
        $output->json($this->monitor->report());

        return 0;
    }
}
