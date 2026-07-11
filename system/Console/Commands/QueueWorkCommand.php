<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Queue\BatchRepository;
use WTD\Queue\FailedJobProvider;
use WTD\Queue\QueueManager;
use WTD\Queue\Worker;

final class QueueWorkCommand implements Command
{
    public function name(): string
    {
        return 'queue:work';
    }

    public function description(): string
    {
        return 'Process the next queued job.';
    }

    public function handle(Input $input, Output $output): int
    {
        $connection = is_string($input->option('connection')) ? (string) $input->option('connection') : 'database';
        $queue = $input->argument(0, 'default') ?? 'default';
        $driver = (new QueueManager($connection))->connection($connection);
        $worker = new Worker($driver, new FailedJobProvider(), new BatchRepository());

        $output->line($worker->runNext($queue) ? 'Processed queued job' : 'No queued jobs available');

        return 0;
    }
}
