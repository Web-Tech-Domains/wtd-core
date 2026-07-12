<?php

declare(strict_types=1);

namespace WTD\Database;

use RuntimeException;

/**
 * Runs pending migrations and rolls back the latest batch.
 */
final class MigrationRunner
{
    /**
     * @var list<string>
     */
    private readonly array $paths;

    /**
     * @param string|list<string> $paths
     */
    public function __construct(
        private readonly MigrationRepository $repository,
        private readonly Schema $schema,
        string|array $paths,
    ) {
        $this->paths = is_string($paths) ? [$paths] : $paths;
    }

    /**
     * Create a runner for another database connection while reusing the migration paths.
     */
    public function forConnection(Connection $connection): self
    {
        return new self(new MigrationRepository($connection), new Schema($connection), $this->paths);
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
        $allFiles = [];

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

            if ($files === false) {
                throw new RuntimeException(sprintf('Unable to read migration path [%s].', $path));
            }

            $allFiles = array_merge($allFiles, $files);
        }

        sort($allFiles);

        return $allFiles;
    }

    private function migration(string $name): Migration
    {
        $foundPath = null;

        foreach ($this->paths as $path) {
            $testPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php';
            if (is_file($testPath)) {
                $foundPath = $testPath;
                break;
            }
        }

        if ($foundPath === null) {
            throw new RuntimeException(sprintf('Migration [%s] was not found.', $name));
        }

        $migration = require $foundPath;

        if (!$migration instanceof Migration) {
            throw new RuntimeException(sprintf('Migration [%s] must return an instance of %s.', $name, Migration::class));
        }

        return $migration;
    }
}
