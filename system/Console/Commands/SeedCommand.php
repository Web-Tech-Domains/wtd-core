<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Database\DatabaseManager;
use WTD\Database\SeederRunner;

/**
 * Runs database seeders.
 */
final class SeedCommand implements Command
{
    public function __construct(
        private readonly SeederRunner $runner,
        private readonly DatabaseManager $database,
    ) {
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
        $ran = $this->runner($input)->run($input->argument(0));

        if ($ran === []) {
            $output->line('Nothing to seed');
            return 0;
        }

        foreach ($ran as $seeder) {
            $output->line('Seeded: ' . $seeder);
        }

        return 0;
    }

    private function runner(Input $input): SeederRunner
    {
        $connection = $input->option('database');

        return is_string($connection)
            ? $this->runner->forConnection($this->database->connection($connection))
            : $this->runner;
    }
}
