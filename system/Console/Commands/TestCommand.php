<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

final class TestCommand implements Command
{
    public function name(): string
    {
        return 'test';
    }

    public function description(): string
    {
        return 'Print or run the project test command.';
    }

    public function handle(Input $input, Output $output): int
    {
        $command = 'composer test';

        if (!$input->hasOption('run')) {
            $output->line($command);
            return 0;
        }

        passthru($command, $status);

        return $status;
    }
}
