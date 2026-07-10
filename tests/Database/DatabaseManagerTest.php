<?php

declare(strict_types=1);

namespace Tests\Database;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\DatabaseServiceProvider;

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
        $thrown = false;

        try {
            $connection->transaction(function (Connection $connection): void {
                $connection->statement('INSERT INTO events (name) VALUES (?)', ['created']);
                throw new RuntimeException('Stop');
            });
        } catch (RuntimeException $exception) {
            $thrown = true;
            self::assertSame('Stop', $exception->getMessage());
        }

        self::assertTrue($thrown);
        self::assertSame([], $connection->select('SELECT name FROM events'));
    }

    public function testManagerRejectsMissingConnections(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new DatabaseManager($this->config()))->connection('missing');
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
