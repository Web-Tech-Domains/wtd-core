<?php

declare(strict_types=1);

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use WTD\Config\Repository;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;

final class QueryBuilderTest extends TestCase
{
    public function testQueryBuilderSelectsRowsWithWhereLimitAndOffset(): void
    {
        $connection = $this->connection();
        $this->seedUsers($connection);

        $rows = $connection->table('users')
            ->select('name')
            ->where('active', 1)
            ->limit(1)
            ->offset(1)
            ->get();

        self::assertSame([['name' => 'Ada']], $rows);
    }

    public function testQueryBuilderReturnsFirstRow(): void
    {
        $connection = $this->connection();
        $this->seedUsers($connection);

        $row = $connection->table('users')
            ->select('name')
            ->where('name', 'Taylor')
            ->first();

        self::assertSame(['name' => 'Taylor'], $row);
    }

    public function testQueryBuilderCanInsertUpdateAndDeleteRows(): void
    {
        $connection = $this->connection();
        $connection->statement('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, active INTEGER NOT NULL)');

        self::assertSame(1, $connection->table('users')->insert([
            'name' => 'Taylor',
            'active' => 0,
        ]));
        self::assertSame(1, $connection->table('users')->where('name', 'Taylor')->update([
            'active' => 1,
        ]));

        self::assertSame([['active' => 1]], $connection->table('users')->select('active')->where('name', 'Taylor')->get());

        self::assertSame(1, $connection->table('users')->where('active', 1)->delete());
        self::assertSame([], $connection->table('users')->get());
    }

    public function testQueryBuilderCompilesSelectSql(): void
    {
        $sql = $this->connection()->table('users')
            ->select('id', 'name')
            ->where('active', '=', 1)
            ->limit(10)
            ->toSql();

        self::assertSame('SELECT "id", "name" FROM "users" WHERE "active" = ? LIMIT 10', $sql);
    }

    private function seedUsers(Connection $connection): void
    {
        $connection->statement('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, active INTEGER NOT NULL)');
        $connection->table('users')->insert(['name' => 'Taylor', 'active' => 1]);
        $connection->table('users')->insert(['name' => 'Ada', 'active' => 1]);
        $connection->table('users')->insert(['name' => 'Grace', 'active' => 0]);
    }

    private function connection(): Connection
    {
        return (new DatabaseManager(new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ])))->connection();
    }
}
