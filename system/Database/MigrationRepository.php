<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Stores applied migration records.
 */
final class MigrationRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Ensure the migrations table exists.
     */
    public function ensureRepository(): void
    {
        $this->connection->statement(
            'CREATE TABLE IF NOT EXISTS "migrations" ("migration" VARCHAR(255) NOT NULL, "batch" INTEGER NOT NULL)',
        );
    }

    /**
     * Return applied migration names.
     *
     * @return list<string>
     */
    public function ran(): array
    {
        $rows = $this->connection->select('SELECT migration FROM "migrations" ORDER BY migration ASC');

        return array_map(static fn (array $row): string => (string) $row['migration'], $rows);
    }

    /**
     * Return migrations from the latest batch.
     *
     * @return list<string>
     */
    public function latestBatch(): array
    {
        $batch = $this->lastBatch();

        if ($batch === 0) {
            return [];
        }

        $rows = $this->connection->select(
            'SELECT migration FROM "migrations" WHERE batch = ? ORDER BY migration DESC',
            [$batch],
        );

        return array_map(static fn (array $row): string => (string) $row['migration'], $rows);
    }

    /**
     * Return the latest batch number.
     */
    public function lastBatch(): int
    {
        $rows = $this->connection->select('SELECT MAX(batch) AS batch FROM "migrations"');

        return (int) ($rows[0]['batch'] ?? 0);
    }

    /**
     * Record an applied migration.
     */
    public function log(string $migration, int $batch): void
    {
        $this->connection->statement(
            'INSERT INTO "migrations" (migration, batch) VALUES (?, ?)',
            [$migration, $batch],
        );
    }

    /**
     * Remove an applied migration record.
     */
    public function delete(string $migration): void
    {
        $this->connection->statement('DELETE FROM "migrations" WHERE migration = ?', [$migration]);
    }
}
