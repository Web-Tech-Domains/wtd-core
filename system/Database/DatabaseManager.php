<?php

declare(strict_types=1);

namespace WTD\Database;

use InvalidArgumentException;
use Closure;
use PDO;
use WTD\Config\Repository;

/**
 * Creates and caches named database connections.
 */
final class DatabaseManager
{
    /**
     * @var array<string, Connection>
     */
    private array $connections = [];

    /**
     * @var array<string, Closure(array<string, mixed>): Connection>
     */
    private array $providers = [];

    public function __construct(private readonly Repository $config)
    {
    }

    /**
     * Resolve the default or named connection.
     */
    public function connection(?string $name = null): Connection
    {
        $name ??= $this->defaultConnection();

        if (!array_key_exists($name, $this->connections)) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Return the configured default connection name.
     */
    public function defaultConnection(): string
    {
        $default = $this->config->get('database.default', 'sqlite');

        if (!is_string($default) || $default === '') {
            throw new InvalidArgumentException('Database default connection must be a non-empty string.');
        }

        return $default;
    }

    /**
     * Forget a cached connection.
     */
    public function purge(?string $name = null): void
    {
        unset($this->connections[$name ?? $this->defaultConnection()]);
    }

    /**
     * Register a custom connection provider for a database driver.
     *
     * @param Closure(array<string, mixed>): Connection $provider
     */
    public function extend(string $driver, Closure $provider): void
    {
        $this->providers[strtolower($driver)] = $provider;
    }

    /**
     * Determine whether a named connection is configured.
     */
    public function hasConnection(string $name): bool
    {
        try {
            $this->connectionConfiguration($name);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Return configured connection names.
     *
     * @return list<string>
     */
    public function connectionNames(): array
    {
        $connections = $this->config->get('database.connections', []);

        if (is_array($connections) && $connections !== []) {
            return array_values(array_filter(array_keys($connections), 'is_string'));
        }

        $names = [];

        foreach ($this->config->all() as $key => $value) {
            if (preg_match('/^database\.connections\.([^.]+)\./', (string) $key, $matches) === 1) {
                $names[] = $matches[1];
            }
        }

        return array_values(array_unique($names));
    }

    private function makeConnection(string $name): Connection
    {
        $configuration = $this->connectionConfiguration($name);

        $driver = strtolower($this->stringValue($configuration['driver'] ?? null));
        if (isset($this->providers[$driver])) {
            return ($this->providers[$driver])($configuration);
        }

        $pdo = new PDO(
            $this->dsn($configuration),
            $this->stringValue($configuration['username'] ?? null),
            $this->stringValue($configuration['password'] ?? null),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        );

        return new Connection($pdo);
    }

    /**
     * @return array<string, mixed>
     */
    private function connectionConfiguration(string $name): array
    {
        $configuration = $this->config->get('database.connections.' . $name);

        if (is_array($configuration)) {
            return $configuration;
        }

        $prefix = 'database.connections.' . $name . '.';
        $configuration = [];

        foreach ($this->config->all() as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $configuration[substr($key, strlen($prefix))] = $value;
            }
        }

        if ($configuration === []) {
            throw new InvalidArgumentException(sprintf('Database connection [%s] is not configured.', $name));
        }

        return $configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function dsn(array $configuration): string
    {
        $driver = strtolower($this->stringValue($configuration['driver'] ?? null));

        return match ($driver) {
            'sqlite' => 'sqlite:' . $this->stringValue($configuration['database'] ?? ':memory:'),
            'mysql', 'mariadb' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->stringValue($configuration['host'] ?? '127.0.0.1'),
                $this->stringValue($configuration['port'] ?? '3306'),
                $this->stringValue($configuration['database'] ?? ''),
                $this->stringValue($configuration['charset'] ?? 'utf8mb4'),
            ),
            'pgsql', 'postgres', 'postgresql' => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $this->stringValue($configuration['host'] ?? '127.0.0.1'),
                $this->stringValue($configuration['port'] ?? '5432'),
                $this->stringValue($configuration['database'] ?? ''),
            ),
            'sqlsrv' => sprintf(
                'sqlsrv:Server=%s,%s;Database=%s',
                $this->stringValue($configuration['host'] ?? '127.0.0.1'),
                $this->stringValue($configuration['port'] ?? '1433'),
                $this->stringValue($configuration['database'] ?? ''),
            ),
            default => throw new InvalidArgumentException(sprintf('Database driver [%s] is not supported.', $driver)),
        };
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
