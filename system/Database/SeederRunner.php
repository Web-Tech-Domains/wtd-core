<?php

declare(strict_types=1);

namespace WTD\Database;

use RuntimeException;

/**
 * Runs database seeders.
 */
final class SeederRunner
{
    /**
     * @var list<string>
     */
    private readonly array $paths;

    /**
     * @param string|list<string> $paths
     */
    public function __construct(
        private readonly Connection $connection,
        string|array $paths,
    ) {
        $this->paths = is_string($paths) ? [$paths] : $paths;
    }

    /**
     * Create a runner for another database connection while reusing the seeder paths.
     */
    public function forConnection(Connection $connection): self
    {
        return new self($connection, $this->paths);
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
        $allSeeders = [];

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

            if ($files === false) {
                throw new RuntimeException(sprintf('Unable to read seeder path [%s].', $path));
            }

            $allSeeders = array_merge($allSeeders, array_map(static fn (string $file): string => basename($file, '.php'), $files));
        }

        sort($allSeeders);

        return $allSeeders;
    }

    private function seeder(string $name): Seeder
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
            throw new RuntimeException(sprintf('Seeder [%s] was not found.', $name));
        }

        $seeder = require $foundPath;

        if (!$seeder instanceof Seeder) {
            throw new RuntimeException(sprintf('Seeder [%s] must return an instance of %s.', $name, Seeder::class));
        }

        return $seeder;
    }
}
