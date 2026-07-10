<?php

declare(strict_types=1);

namespace WTD\Database;

use RuntimeException;

/**
 * Runs pending migrations and rolls back the latest batch.
 */
final class MigrationRunner
{
    public function __construct(
        private readonly MigrationRepository $repository,
        private readonly Schema $schema,
        private readonly string $path,
    ) {
    }

    /**
     * Run all pending migrations.
     *
     * @return list<string>
     */
    public function migrate(): array
    {
        $this->repository->ensureRepository();
        $pending = $this->pending();
        $batch = $this->repository->lastBatch() + 1;
        $ran = [];

        foreach ($pending as $migration) {
            $this->migration($migration)->up($this->schema);
            $this->repository->log($migration, $batch);
            $ran[] = $migration;
        }

        return $ran;
    }

    /**
     * Roll back the latest migration batch.
     *
     * @return list<string>
     */
    public function rollback(): array
    {
        $this->repository->ensureRepository();
        $rolledBack = [];

        foreach ($this->repository->latestBatch() as $migration) {
            $this->migration($migration)->down($this->schema);
            $this->repository->delete($migration);
            $rolledBack[] = $migration;
        }

        return $rolledBack;
    }

    /**
     * Return pending migration names.
     *
     * @return list<string>
     */
    public function pending(): array
    {
        $files = $this->files();
        $ran = $this->repository->ran();

        return array_values(array_filter(
            array_map(static fn (string $file): string => basename($file, '.php'), $files),
            static fn (string $migration): bool => !in_array($migration, $ran, true),
        ));
    }

    /**
     * @return list<string>
     */
    private function files(): array
    {
        if (!is_dir($this->path)) {
            return [];
        }

        $files = glob(rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            throw new RuntimeException(sprintf('Unable to read migration path [%s].', $this->path));
        }

        sort($files);

        return array_values($files);
    }

    private function migration(string $name): Migration
    {
        $path = rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php';

        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Migration [%s] was not found.', $name));
        }

        $migration = require $path;

        if (!$migration instanceof Migration) {
            throw new RuntimeException(sprintf('Migration [%s] must return an instance of %s.', $name, Migration::class));
        }

        return $migration;
    }
}
