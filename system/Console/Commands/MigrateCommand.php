<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Database\DatabaseManager;
use WTD\Database\MigrationRunner;

/**
 * Runs pending database migrations.
 */
final class MigrateCommand implements Command
{
    public function __construct(
        private readonly MigrationRunner $runner,
        private readonly DatabaseManager $database,
    ) {
    }

    public function name(): string
    {
        return 'migrate';
    }

    public function description(): string
    {
        return 'Run pending database migrations.';
    }

    public function handle(Input $input, Output $output): int
    {
        $ran = $this->runner($input)->migrate();

        if ($ran === []) {
            $output->line('Nothing to migrate');
            return 0;
        }

        foreach ($ran as $migration) {
            $output->line('Migrated: ' . $migration);
        }

        return 0;
    }

    private function runner(Input $input): MigrationRunner
    {
        $connection = $input->option('database');

        return is_string($connection)
            ? $this->runner->forConnection($this->database->connection($connection))
            : $this->runner;
    }
}
