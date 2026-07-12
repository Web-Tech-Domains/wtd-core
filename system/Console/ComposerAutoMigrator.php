<?php

declare(strict_types=1);

namespace WTD\Console;

/**
 * Runs framework migrations from Composer lifecycle hooks.
 */
final class ComposerAutoMigrator
{
    /**
     * @param non-empty-string $basePath
     */
    public function __construct(private readonly string $basePath)
    {
    }

    public function run(): int
    {
        $env = $this->loadEnv($this->basePath . DIRECTORY_SEPARATOR . '.env');

        if (!$this->enabled($env)) {
            echo "Auto migration skipped because WTD_AUTO_MIGRATE is disabled.\n";
            return 0;
        }

        $connection = $env['DB_CONNECTION'] ?? 'sqlite';
        $database = $env['DB_DATABASE'] ?? ':memory:';

        if ($connection === 'sqlite' && $database !== ':memory:') {
            $exitCode = $this->ensureSqliteDatabase($database);

            if ($exitCode !== 0) {
                return $exitCode;
            }
        }

        return $this->migrate();
    }

    /**
     * @param array<string, string> $env
     */
    private function enabled(array $env): bool
    {
        $enabled = $_SERVER['WTD_AUTO_MIGRATE'] ?? false;

        if ($enabled === false || $enabled === '') {
            $enabled = getenv('WTD_AUTO_MIGRATE');
        }

        if ($enabled === false || $enabled === '') {
            $enabled = $env['WTD_AUTO_MIGRATE'] ?? 'true';
        }

        $enabled = strtolower((string) $enabled);

        return !in_array($enabled, ['0', 'false', 'no', 'off'], true);
    }

    private function ensureSqliteDatabase(string $database): int
    {
        $databasePath = $this->absolutePath($database);
        $databaseDirectory = dirname($databasePath);

        if (!is_dir($databaseDirectory) && !mkdir($databaseDirectory, 0775, true) && !is_dir($databaseDirectory)) {
            fwrite(STDERR, sprintf("Unable to create SQLite database directory [%s].\n", $databaseDirectory));
            return 1;
        }

        if (!is_file($databasePath) && file_put_contents($databasePath, '') === false) {
            fwrite(STDERR, sprintf("Unable to create SQLite database file [%s].\n", $databasePath));
            return 1;
        }

        return 0;
    }

    private function migrate(): int
    {
        $command = escapeshellarg(PHP_BINARY)
            . ' -d display_errors=1 '
            . escapeshellarg($this->basePath . DIRECTORY_SEPARATOR . 'core')
            . ' migrate';
        passthru($command, $exitCode);

        if ($exitCode !== 0) {
            fwrite(
                STDERR,
                sprintf(
                    "Auto migration failed with exit code [%d]. Run [php core migrate] on the server to see the database error, or set WTD_AUTO_MIGRATE=false to skip migrations during dependency installation.\n",
                    $exitCode,
                ),
            );
        }

        return $exitCode;
    }

    /**
     * @return array<string, string>
     */
    private function loadEnv(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $values = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return [];
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);

            if ($key === '') {
                continue;
            }

            $values[$key] = trim(trim($value), "\"'");
        }

        return $values;
    }

    private function absolutePath(string $path): string
    {
        if ($path === '') {
            return $this->basePath;
        }

        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1 || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return $path;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
