<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Database\SeederRunner;

/**
 * Runs database seeders.
 */
final class SeedCommand implements Command
{
    public function __construct(private readonly SeederRunner $runner)
    {
    }

    public function name(): string
    {
        return 'db:seed';
    }

    public function description(): string
    {
        return 'Run database seeders.';
    }

    public function handle(Input $input, Output $output): int
    {
        $ran = $this->runner->run($input->argument(0));

        if ($ran === []) {
            $output->line('Nothing to seed');
            return 0;
        }

        foreach ($ran as $seeder) {
            $output->line('Seeded: ' . $seeder);
        }

        return 0;
    }
}
