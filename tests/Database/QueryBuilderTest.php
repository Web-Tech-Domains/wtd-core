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
        $query = $connection->table('users')->select('name')->where('active', 1);

        $row = $query->first();

        self::assertSame(['name' => 'Taylor'], $row);
        self::assertSame([
            ['name' => 'Taylor'],
            ['name' => 'Ada'],
        ], $query->get());
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

    public function testQueryBuilderCountsMatchingRows(): void
    {
        $connection = $this->connection();
        $this->seedUsers($connection);

        self::assertSame(2, $connection->table('users')->where('active', 1)->count());
    }

    public function testQueryBuilderPaginatesRows(): void
    {
        $connection = $this->connection();
        $this->seedUsers($connection);

        $page = $connection->table('users')
            ->select('name')
            ->paginate(perPage: 2, page: 2);

        self::assertSame([['name' => 'Grace']], $page->items());
        self::assertSame(3, $page->total());
        self::assertSame(2, $page->perPage());
        self::assertSame(2, $page->currentPage());
        self::assertSame(2, $page->lastPage());
        self::assertFalse($page->hasMorePages());
        self::assertSame(3, $page->toArray()['total']);
    }

    public function testQueryBuilderChunksRows(): void
    {
        $connection = $this->connection();
        $this->seedUsers($connection);
        $chunks = [];

        $connection->table('users')->select('name')->chunk(2, /**
         * @param list<array<string, mixed>> $rows
         */
            function (array $rows, int $page) use (&$chunks): void {
                $chunks[$page] = $rows;
            });

        self::assertSame([
            1 => [
                ['name' => 'Taylor'],
                ['name' => 'Ada'],
            ],
            2 => [
                ['name' => 'Grace'],
            ],
        ], $chunks);
    }

    public function testQueryBuilderCanStopChunkingEarly(): void
    {
        $connection = $this->connection();
        $this->seedUsers($connection);
        $pages = [];

        $connection->table('users')->select('name')->chunk(1, /**
         * @param list<array<string, mixed>> $rows
         */
            function (array $rows, int $page) use (&$pages): bool {
                $pages[] = $page;

                return false;
            });

        self::assertSame([1], $pages);
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
