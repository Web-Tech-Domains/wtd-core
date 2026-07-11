<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Scheduler\Scheduler;

final class ScheduleRunCommand implements Command
{
    public function __construct(private readonly Scheduler $scheduler)
    {
    }

    public function name(): string
    {
        return 'schedule:run';
    }

    public function description(): string
    {
        return 'Run due scheduled tasks.';
    }

    public function handle(Input $input, Output $output): int
    {
        $ran = $this->scheduler->runDue(maintenanceMode: $input->hasOption('maintenance'));

        if ($ran === []) {
            $output->line('No scheduled tasks are due');
            return 0;
        }

        foreach ($ran as $event) {
            $output->line('Ran: ' . $event);
        }

        return 0;
    }
}
