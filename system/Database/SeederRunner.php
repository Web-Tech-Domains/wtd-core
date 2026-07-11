<?php

declare(strict_types=1);

namespace WTD\Database;

use RuntimeException;

/**
 * Runs database seeders.
 */
final class SeederRunner
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $path,
    ) {
    }

    /**
     * Create a runner for another database connection while reusing the seeder path.
     */
    public function forConnection(Connection $connection): self
    {
        return new self($connection, $this->path);
    }

    /**
     * Run all seeders or a specific seeder by file name.
     *
     * @return list<string>
     */
    public function run(?string $name = null): array
    {
        $seeders = $name === null ? $this->seeders() : [$name];
        $ran = [];

        foreach ($seeders as $seeder) {
            $this->seeder($seeder)->run($this->connection);
            $ran[] = $seeder;
        }

        return $ran;
    }

    /**
     * Return available seeder names.
     *
     * @return list<string>
     */
    public function seeders(): array
    {
        if (!is_dir($this->path)) {
            return [];
        }

        $files = glob(rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            throw new RuntimeException(sprintf('Unable to read seeder path [%s].', $this->path));
        }

        sort($files);

        return array_values(array_map(static fn (string $file): string => basename($file, '.php'), $files));
    }

    private function seeder(string $name): Seeder
    {
        $path = rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php';

        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Seeder [%s] was not found.', $name));
        }

        $seeder = require $path;

        if (!$seeder instanceof Seeder) {
            throw new RuntimeException(sprintf('Seeder [%s] must return an instance of %s.', $name, Seeder::class));
        }

        return $seeder;
    }
}
