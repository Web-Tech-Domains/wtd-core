<?php

declare(strict_types=1);

namespace WTD\Database;

use Closure;

/**
 * Provides basic database schema operations.
 */
final class Schema
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Create a table.
     *
     * @param Closure(Blueprint): void $definition
     */
    public function create(string $table, Closure $definition): void
    {
        $blueprint = new Blueprint($table);
        $definition($blueprint);

        $columns = $blueprint->columns();

        if ($columns === []) {
            $columns[] = '"id" INTEGER PRIMARY KEY AUTOINCREMENT';
        }

        $this->connection->statement(sprintf(
            'CREATE TABLE %s (%s)',
            $this->quote($blueprint->table()),
            implode(', ', $columns),
        ));
    }

    /**
     * Drop a table when it exists.
     */
    public function dropIfExists(string $table): void
    {
        $this->connection->statement(sprintf('DROP TABLE IF EXISTS %s', $this->quote($table)));
    }

    /**
     * Determine whether a table exists.
     */
    public function hasTable(string $table): bool
    {
        $rows = $this->connection->select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = ?",
            [$table],
        );

        return $rows !== [];
    }

    private function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
