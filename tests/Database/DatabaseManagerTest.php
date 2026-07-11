<?php

declare(strict_types=1);

namespace Tests\Database;

use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\DatabaseServiceProvider;
use WTD\Database\QueryExecuted;

final class DatabaseManagerTest extends TestCase
{
    public function testDatabaseManagerCachesDefaultConnection(): void
    {
        $manager = new DatabaseManager($this->config());

        self::assertSame($manager->connection(), $manager->connection('sqlite'));
    }

    public function testConnectionRunsStatementsAndSelectsRows(): void
    {
        $connection = (new DatabaseManager($this->config()))->connection();

        $connection->statement('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');
        $connection->statement('INSERT INTO users (name) VALUES (:name)', ['name' => 'Taylor']);

        $rows = $connection->select('SELECT name FROM users WHERE id = ?', [1]);

        self::assertSame([['name' => 'Taylor']], $rows);
    }

    public function testConnectionDispatchesQueryExecutedEvents(): void
    {
        $connection = (new DatabaseManager($this->config()))->connection();
        $events = [];

        $connection->listen(static function (QueryExecuted $event) use (&$events): void {
            $events[] = $event;
        });

        $connection->statement('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');
        $connection->statement('INSERT INTO users (name) VALUES (:name)', ['name' => 'Taylor']);

        self::assertCount(2, $events);
        self::assertSame('INSERT INTO users (name) VALUES (:name)', $events[1]->sql);
        self::assertSame(['name' => 'Taylor'], $events[1]->bindings);
        self::assertSame($connection, $events[1]->connection);
        self::assertGreaterThanOrEqual(0.0, $events[1]->timeMs);
    }

    public function testConnectionCommitsTransactions(): void
    {
        $connection = (new DatabaseManager($this->config()))->connection();
        $connection->statement('CREATE TABLE events (name TEXT NOT NULL)');

        $connection->transaction(function (Connection $connection): void {
            $connection->statement('INSERT INTO events (name) VALUES (?)', ['created']);
        });

        self::assertSame([['name' => 'created']], $connection->select('SELECT name FROM events'));
    }

    public function testConnectionRollsBackFailedTransactions(): void
    {
        $connection = (new DatabaseManager($this->config()))->connection();
        $connection->statement('CREATE TABLE events (name TEXT NOT NULL)');
        $thrown = null;

        try {
            $connection->transaction(function (Connection $connection): void {
                $connection->statement('INSERT INTO events (name) VALUES (?)', ['created']);
                throw new RuntimeException('Stop');
            });
        } catch (RuntimeException $exception) {
            $thrown = $exception;
        }

        self::assertInstanceOf(RuntimeException::class, $thrown);
        self::assertSame('Stop', $thrown->getMessage());
        self::assertSame([], $connection->select('SELECT name FROM events'));
    }

    public function testManagerRejectsMissingConnections(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new DatabaseManager($this->config()))->connection('missing');
    }

    public function testDatabaseManagerBuildsSupportedDriverDsns(): void
    {
        $manager = new DatabaseManager($this->config());
        $method = new \ReflectionMethod(DatabaseManager::class, 'dsn');

        self::assertSame('sqlite::memory:', $method->invoke($manager, [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]));
        self::assertSame('mysql:host=127.0.0.1;port=3306;dbname=wtd;charset=utf8mb4', $method->invoke($manager, [
            'driver' => 'mariadb',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'wtd',
            'charset' => 'utf8mb4',
        ]));
        self::assertSame('pgsql:host=127.0.0.1;port=5432;dbname=wtd', $method->invoke($manager, [
            'driver' => 'postgresql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'wtd',
        ]));
        self::assertSame('sqlsrv:Server=127.0.0.1,1433;Database=wtd', $method->invoke($manager, [
            'driver' => 'sqlsrv',
            'host' => '127.0.0.1',
            'port' => '1433',
            'database' => 'wtd',
        ]));
    }

    public function testDatabaseManagerListsConnectionsAndSupportsCustomProviders(): void
    {
        $manager = new DatabaseManager(new Repository([
            'database.default' => 'custom',
            'database.connections.custom.driver' => 'array',
            'database.connections.reporting.driver' => 'sqlite',
            'database.connections.reporting.database' => ':memory:',
        ]));
        $manager->extend('array', static fn (array $configuration): Connection => new Connection(new PDO('sqlite::memory:')));

        self::assertSame(['custom', 'reporting'], $manager->connectionNames());
        self::assertTrue($manager->hasConnection('custom'));
        self::assertFalse($manager->hasConnection('missing'));
        self::assertInstanceOf(Connection::class, $manager->connection('custom'));
    }

    public function testDatabaseServiceProviderRegistersServices(): void
    {
        /** @var non-empty-string $basePath */
        $basePath = dirname(__DIR__, 2);
        $app = new Application($basePath, new Container(), $this->config());
        $app->register(DatabaseServiceProvider::class);

        self::assertInstanceOf(DatabaseManager::class, $app->container()->get(DatabaseManager::class));
        self::assertInstanceOf(Connection::class, $app->container()->get(Connection::class));
    }

    private function config(): Repository
    {
        return new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
    }
}
