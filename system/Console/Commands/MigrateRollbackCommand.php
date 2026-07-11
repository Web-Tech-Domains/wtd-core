<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Database\DatabaseManager;
use WTD\Database\MigrationRunner;

/**
 * Rolls back the latest database migration batch.
 */
final class MigrateRollbackCommand implements Command
{
    public function __construct(
        private readonly MigrationRunner $runner,
        private readonly DatabaseManager $database,
    ) {
    }

    public function name(): string
    {
        return 'migrate:rollback';
    }

    public function description(): string
    {
        return 'Roll back the latest database migration batch.';
    }

    public function handle(Input $input, Output $output): int
    {
        $rolledBack = $this->runner($input)->rollback();

        if ($rolledBack === []) {
            $output->line('Nothing to roll back');
            return 0;
        }

        foreach ($rolledBack as $migration) {
            $output->line('Rolled back: ' . $migration);
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
